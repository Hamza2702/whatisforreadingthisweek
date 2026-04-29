<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classroom; 
use App\Models\Student;
use App\Models\Book;  
use Illuminate\Support\Facades\DB;
use App\Models\StudentReadingList;
use App\Models\StudentFavouriteBook;
use Illuminate\Support\Facades\Cache;

class ReadingController extends Controller
{
    // Show page and generate choices for each student
    public function generateList(Classroom $classroom)
    {   
        $yearGroups = []; 
        // get students and genres and weekly goals and books
        $students = $classroom->students()->with(['user', 'preferredGenres', 'weeklyGoal', 'books'])->get();
        // if no students
        if ($students->isEmpty()) {
            return view('teacher.classes.reading-list', compact('classroom', 'students', 'yearGroups'));
        }

        $studentIds = $students->pluck('id')->toArray();
        $schoolId = $students->first()?->school_id;

        // check if generate all has already been used this week for this classroom
        $generateAllUsedThisWeek = $this->hasGenerateAllRunThisWeek($classroom->id, $studentIds);

        // load reading lists
        $allReadingListEntries = StudentReadingList::whereIn('student_id', $studentIds)
            ->with(['book.genres', 'book.phonics'])
            ->get()
            ->groupBy('student_id');

        // load 5 last reviews of recent difficulty feedback for each student
        $recentDifficulties = DB::table('book_reviews')
            ->whereIn('student_id', $studentIds)
            ->select('student_id', 'difficulty', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('student_id')
            ->map(function ($reviews) {
                return $this->calculateDifficultyBias($reviews->take(5));
            });

        // load every books difficulty based off student reviews
        $bookDifficultyMap = $this->buildBookDifficultyMap();

        // colours
        $colourBands = [
            'Light Purple', 'Pink', 'Red', 'Yellow', 'Light Blue', 'Green', 'Orange', 
            'Turquoise', 'Purple', 'Gold', 'White', 'Lime', 'Lime+', 'Grey', 'Dark Blue', 'Dark Red'
        ];

        // check what colours are needed to get
        $neededColours = [];
        // loop through students
        foreach ($students as $student) {
            // convert level to oxford colour
            $student->ort_colour = $this->getOxfordColour($student->level);
            // find index of students colour
            $currentIndex = array_search($student->ort_colour, $colourBands);
            if ($currentIndex === false) $currentIndex = 1;

            $neededColours[] = $colourBands[$currentIndex]; // same level
            if ($currentIndex > 1) $neededColours[] = $colourBands[$currentIndex - 1]; // level below
            if ($currentIndex < count($colourBands) - 1) $neededColours[] = $colourBands[$currentIndex + 1]; // level above
        }
        $neededColours = array_unique($neededColours);

        // find banned books
        $bannedBookIds = $schoolId ? DB::table('book_school_ban')
            ->where('school_id', $schoolId)
            ->pluck('book_id')
            ->toArray() : [];

        // native db join + chunk by id
        $availableBooks = [];
        if ($schoolId && !empty($neededColours)) {
            $query = Book::select('books.id', 'books.ort_colour')
                ->join('book_school_stocks', 'books.id', '=', 'book_school_stocks.book_id')
                ->where('book_school_stocks.school_id', $schoolId)
                ->where('book_school_stocks.stock', '>', 0)
                ->whereIn('books.ort_colour', $neededColours);

            if (!empty($bannedBookIds)) {
                $query->whereNotIn('books.id', $bannedBookIds);
            }

            // chunk books with id, also tag each book with its peer reviewed difficulty if known
            $query->with('genres:id')->chunkById(1000, function ($books) use (&$availableBooks, $bookDifficultyMap) {
                foreach ($books as $book) {
                    $availableBooks[$book->ort_colour][] = [
                        'id' => $book->id,
                        'colour' => $book->ort_colour,
                        'genres' => $book->genres->pluck('id')->toArray(),
                        'peer_difficulty' => $bookDifficultyMap[$book->id] ?? null, // easy/okay/hard or null if not enough reviews
                    ];
                }
            }, 'books.id', 'id'); // use book's id and use id as an alias
        }

        // select book ids for each student
        $allSelectedBookIds = [];
        $studentSelections = [];

        // loop through students
        foreach ($students as $student) {
            // get reading list entries for student
            $entries = $allReadingListEntries->get($student->id, collect());
            $student->readingListEntries = $entries;
            // extract actual books from entries
            $student->readingListBooks = $entries->pluck('book')->filter();

            // get already read book ids
            $alreadyReadOrReadingIds = $student->books->pluck('id')->toArray();

            // get students liked genre ids
            $likedGenresIds = $student->preferredGenres->pluck('id')->toArray();

            // find index of students colour
            $currentIndex = array_search($student->ort_colour, $colourBands);
            if ($currentIndex === false) $currentIndex = 1; 

            // get students difficulty bias: -1 = wants easier, 0 = neutral, +1 = wants harder
            $difficultyBias = $recentDifficulties->get($student->id, 0);

            // find out which peer difficulty to prefer for student
            $preferredPeerDifficulty = $this->mapBiasToPeerDifficulty($difficultyBias);

            // colour bands
            $canGoBelow = $currentIndex > 1;
            $belowColour = $canGoBelow ? $colourBands[$currentIndex - 1] : null;
            $sameColour  = $colourBands[$currentIndex];
            $aboveColour = $currentIndex < count($colourBands) - 1 ? $colourBands[$currentIndex + 1] : $sameColour;

            // how many books to fetch for each category
            $limits = [
                'below_liked'   => $canGoBelow ? 2 : 0,
                'same_liked'    => $canGoBelow ? 2 : 4, 
                'above_liked'   => 2,
                'same_unliked'  => 2,
                'above_unliked' => 2,
            ];

            // adjust limits based on difficulty bias from feedback
            // if student finds books too hard, give them more books from below + same colour
            if ($difficultyBias < 0 && $canGoBelow) {
                $limits['below_liked']   += 2; 
                $limits['above_liked']   = max(0, $limits['above_liked'] - 1);
                $limits['above_unliked'] = max(0, $limits['above_unliked'] - 1);
            }
            // if student finds books too easy, give them more books from above colour
            elseif ($difficultyBias > 0) {
                $limits['above_liked']   += 1;
                $limits['above_unliked'] += 1;
                $limits['below_liked']   = max(0, $limits['below_liked'] - 1);
            }

            // array to hold selected book ids for this specific student
            $selectedIdsForStudent = [];

            // fetch books based on filters
            $fetchBooks = function($targetColour, $isLiked, $limit) use ($likedGenresIds, &$selectedIdsForStudent, $availableBooks, $alreadyReadOrReadingIds, $preferredPeerDifficulty) {
                if ($limit <= 0 || !$targetColour || empty($availableBooks[$targetColour])) return;

                $available = collect($availableBooks[$targetColour])->reject(function($book) use ($selectedIdsForStudent, $alreadyReadOrReadingIds) {
                    return in_array($book['id'], $selectedIdsForStudent) || in_array($book['id'], $alreadyReadOrReadingIds);
                });

                if (!empty($likedGenresIds)) {
                    $available = $available->filter(function($book) use ($likedGenresIds, $isLiked) {
                        $hasLikedGenre = !empty(array_intersect($book['genres'], $likedGenresIds));
                        return $isLiked ? $hasLikedGenre : !$hasLikedGenre;
                    });
                }

                // prioritise books that match the students preferred peer difficulty
                // books with no peer reviews are kept as a neutral fallback
                $matching = $available->filter(function($book) use ($preferredPeerDifficulty) {
                    return $book['peer_difficulty'] === $preferredPeerDifficulty;
                });
                $unrated = $available->filter(function($book) {
                    return $book['peer_difficulty'] === null;
                });
                $other = $available->filter(function($book) use ($preferredPeerDifficulty) {
                    return $book['peer_difficulty'] !== null && $book['peer_difficulty'] !== $preferredPeerDifficulty;
                });

                // take from matching first, then fall back to unrated, then anything else
                $ordered = $matching->shuffle()
                    ->concat($unrated->shuffle())
                    ->concat($other->shuffle());

                $picked = $ordered->take($limit)->pluck('id')->toArray();
                $selectedIdsForStudent = array_merge($selectedIdsForStudent, $picked);
            };

            // fetch books based on priority order
            $fetchBooks($belowColour, true, $limits['below_liked']);   
            $fetchBooks($sameColour,  true, $limits['same_liked']);     
            $fetchBooks($aboveColour, true, $limits['above_liked']);    
            $fetchBooks($sameColour,  false, $limits['same_unliked']);  
            $fetchBooks($aboveColour, false, $limits['above_unliked']); 

            // if not enough books to fill up to 10 books
            $target = $student->weeklyGoal->target ?? 1;
            $booksNeeded = $target * 10;
            $missingCount = $booksNeeded - count($selectedIdsForStudent);
            if ($missingCount > 0) {
                $searchColours = array_filter([$belowColour, $sameColour, $aboveColour]);
                
                $fillerPool = collect();
                foreach ($searchColours as $color) {
                    if (!empty($availableBooks[$color])) {
                        $fillerPool = $fillerPool->concat($availableBooks[$color]);
                    }
                }

                $fillerPicked = $fillerPool->reject(function($book) use ($selectedIdsForStudent, $alreadyReadOrReadingIds) {
                    return in_array($book['id'], $selectedIdsForStudent) || in_array($book['id'], $alreadyReadOrReadingIds);
                })->shuffle()->take($missingCount)->pluck('id')->toArray();

                $selectedIdsForStudent = array_merge($selectedIdsForStudent, $fillerPicked);
            }

            // store student's final selection
            $studentSelections[$student->id] = $selectedIdsForStudent;
            $allSelectedBookIds = array_merge($allSelectedBookIds, $selectedIdsForStudent);
        }

        // fetch the actual eloquent models based on all selected ids
        $allSelectedBookIds = array_unique($allSelectedBookIds);
        
        $finalLoadedBooks = collect();
        if (!empty($allSelectedBookIds)) {
            $finalLoadedBooks = Book::with(['genres', 'phonics'])
                ->whereIn('id', $allSelectedBookIds)
                ->get()
                ->keyBy('id');
        }

        // add recommended books to student
        foreach ($students as $student) {
            $assignedIds = $studentSelections[$student->id] ?? [];
            
            $student->recommendedBooks = collect($assignedIds)->map(function($id) use ($finalLoadedBooks, $bookDifficultyMap) {
                $book = $finalLoadedBooks->get($id);
                // attach the peer difficulty so the view can show the easy/okay/hard badge
                if ($book) {
                    $book->peer_difficulty = $bookDifficultyMap[$book->id] ?? null;
                }
                return $book;
            })->filter();
        }

        return view('teacher.classes.reading-list', compact('classroom', 'students', 'yearGroups', 'generateAllUsedThisWeek'));
    }

    // Generate books for all students
    public function generateAll(Request $request, Classroom $classroom)
    {
        // get students with genres and current books
        $students = $classroom->students()->with(['preferredGenres', 'books', 'weeklyGoal'])->get();
        if ($students->isEmpty()) return back()->with('success', 'No students in classroom.');

        $schoolId = $students->first()?->school_id;
        $studentIds = $students->pluck('id')->toArray();

        // block generate all if its already been used this week for this classroom
        if ($this->hasGenerateAllRunThisWeek($classroom->id, $studentIds)) {
            return back()->with('error', 'Books have already been assigned to this class this week. Use manual assignment for individual students who need more books.');
        }

        // load 5 last reviews of recent difficulty feedback for each student
        $recentDifficulties = DB::table('book_reviews')
            ->whereIn('student_id', $studentIds)
            ->select('student_id', 'difficulty', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('student_id')
            ->map(function ($reviews) {
                return $this->calculateDifficultyBias($reviews->take(5));
            });

       // load every books difficulty based off student reviews
        $bookDifficultyMap = $this->buildBookDifficultyMap();

        // colours
        $colourBands = [
            'Light Purple', 'Pink', 'Red', 'Yellow', 'Light Blue', 'Green', 'Orange', 
            'Turquoise', 'Purple', 'Gold', 'White', 'Lime', 'Lime+', 'Grey', 'Dark Blue', 'Dark Red'
        ];

        // check what colours are needed across the class
        $neededColours = [];
        foreach ($students as $student) {
            $neededColours[] = $this->getOxfordColour($student->level);
        }
        $neededColours = array_unique($neededColours);

        // available stock tracking
        $bannedBookIds = $schoolId ? DB::table('book_school_ban')->where('school_id', $schoolId)->pluck('book_id')->toArray() : [];
        $allStock = DB::table('book_school_stocks')->where('school_id', $schoolId)->pluck('stock', 'book_id');
        
        $readingCounts = DB::table('book_student')
            ->where('school_id', $schoolId)
            ->where('status', 'reading')
            ->select('book_id', DB::raw('count(*) as count'))
            ->groupBy('book_id')
            ->pluck('count', 'book_id');

        $availableStockTracker = [];
        $unavailableBookIds = $bannedBookIds; 

        foreach($allStock as $bookId => $totalStock) {
            $reading = $readingCounts->get($bookId, 0);
            $available = $totalStock - $reading;
            
            if ($available <= 0) {
                $unavailableBookIds[] = $bookId; 
            } else {
                $availableStockTracker[$bookId] = $available; 
            }
        }

        // fetch reading lists
        $readingLists = DB::table('student_reading_lists')
            ->join('books', 'student_reading_lists.book_id', '=', 'books.id') 
            ->whereIn('student_reading_lists.student_id', $studentIds)
            ->select('student_reading_lists.student_id', 'student_reading_lists.book_id', 'books.ort_colour as book_colour')
            ->orderBy('student_reading_lists.id', 'asc') 
            ->get()
            ->groupBy('student_id');

        // create available books, also tag each book with its peer reviewed difficulty
        $availableBooks = [];
        if ($schoolId && !empty($neededColours)) {
            $query = Book::select('books.id', 'books.ort_colour')
                ->join('book_school_stocks', 'books.id', '=', 'book_school_stocks.book_id')
                ->where('book_school_stocks.school_id', $schoolId)
                ->where('book_school_stocks.stock', '>', 0)
                ->whereIn('books.ort_colour', $neededColours);

            if (!empty($unavailableBookIds)) {
                $query->whereNotIn('books.id', $unavailableBookIds);
            }

            $query->with('genres:id')->chunkById(1000, function($books) use (&$availableBooks, $bookDifficultyMap) {
                foreach($books as $book) {
                    $availableBooks[$book->ort_colour][] = [
                        'id' => $book->id,
                        'genres' => $book->genres->pluck('id')->toArray(),
                        'peer_difficulty' => $bookDifficultyMap[$book->id] ?? null,
                    ];
                }
            }, 'books.id', 'id');
        }

        $booksToAssign = [];
        $readingListBooksToRemove = [];

        // loop through students
        foreach ($students as $student) {
            $target = $student->weeklyGoal->target ?? 1;
            $target = min(max($target, 1), 3);

            // get already read book ids
            $alreadyReadIds = $student->books->pluck('id')->toArray();
            $assignedToThisStudent = [];

            // get students colour level
            $ortColour = $this->getOxfordColour($student->level);
            $studentColourIndex = array_search($ortColour, $colourBands);
            if ($studentColourIndex === false) $studentColourIndex = 1;

            // get students difficulty bias: -1 = wants easier, 0 = neutral, +1 = wants harder
            $difficultyBias = $recentDifficulties->get($student->id, 0);

            /// find out which peer difficulty to prefer for student
            $preferredPeerDifficulty = $this->mapBiasToPeerDifficulty($difficultyBias);

            // change target colour based on difficulty feedback
            $targetColourIndex = $studentColourIndex;
            if ($difficultyBias < 0 && $studentColourIndex > 1) {
                $targetColourIndex = $studentColourIndex - 1; // give them easier book
            } elseif ($difficultyBias > 0 && $studentColourIndex < count($colourBands) - 1) {
                $targetColourIndex = $studentColourIndex + 1; // give them harder book
            }
            $targetColour = $colourBands[$targetColourIndex];

            // try matching a book from reading list first
            if ($readingLists->has($student->id)) {
                $listBooks = $readingLists->get($student->id);

                foreach ($listBooks as $listItem) {
                    if (count($assignedToThisStudent) >= $target) break;

                    $bookColourIndex = array_search($listItem->book_colour, $colourBands);
                    if ($bookColourIndex === false) $bookColourIndex = 1;

                    $levelDifference = abs($bookColourIndex - $studentColourIndex);

                    if ($levelDifference > 1) {
                        StudentFavouriteBook::updateOrCreate(
                            ['student_id' => $student->id, 'book_id' => $listItem->book_id],
                            ['school_id' => $student->school_id, 'classroom_id' => $student->classroom_id]
                        );

                        DB::table('student_reading_lists')
                            ->where('student_id', $student->id)
                            ->where('book_id', $listItem->book_id)
                            ->delete();
                        continue; 
                    }

                    if (in_array($listItem->book_id, $alreadyReadIds)) {
                        DB::table('student_reading_lists')
                            ->where('student_id', $student->id)
                            ->where('book_id', $listItem->book_id)
                            ->delete();
                        continue; 
                    }

                    if (in_array($listItem->book_id, $unavailableBookIds)) {
                        continue; 
                    }

                    // assign book if found
                    $assignedToThisStudent[] = ['id' => $listItem->book_id];
                    $alreadyReadIds[] = $listItem->book_id; 

                    $readingListBooksToRemove[] = [
                        'student_id' => $student->id,
                        'book_id' => $listItem->book_id,
                    ];

                    if (isset($availableStockTracker[$listItem->book_id])) {
                        $availableStockTracker[$listItem->book_id]--;
                        if ($availableStockTracker[$listItem->book_id] <= 0) {
                            $unavailableBookIds[] = $listItem->book_id;
                        }
                    }
                }
            }

            // get liked genres
            $likedGenresIds = $student->preferredGenres->pluck('id')->toArray();

            // fall back, any unread book in the same colour that isnt banned
            while (count($assignedToThisStudent) < $target) {
                $selectedBookData = null;

                // try the difficulty adjusted colour first, then fall back to the original colour
                $colourPriority = $targetColour !== $ortColour 
                    ? [$targetColour, $ortColour] 
                    : [$ortColour];

                foreach ($colourPriority as $tryColour) {
                    if (!empty($availableBooks[$tryColour])) {
                        $availableForStudent = collect($availableBooks[$tryColour])->reject(function($book) use ($alreadyReadIds, $unavailableBookIds) {
                            return in_array($book['id'], $alreadyReadIds) || in_array($book['id'], $unavailableBookIds);
                        });

                        if ($availableForStudent->isEmpty()) continue;

                        $preferredBooks = $availableForStudent->filter(function($book) use ($likedGenresIds) {
                            return !empty(array_intersect($book['genres'], $likedGenresIds));
                        });

                        // start with genre matched pool first, otherwise fall back to all available
                        $pool = $preferredBooks->isNotEmpty() ? $preferredBooks : $availableForStudent;

                        // prefer books with peer difficulty matching the students preferred difficulty
                        $matching = $pool->filter(function($book) use ($preferredPeerDifficulty) {
                            return $book['peer_difficulty'] === $preferredPeerDifficulty;
                        });
                        $unrated = $pool->filter(function($book) {
                            return $book['peer_difficulty'] === null;
                        });
                        $other = $pool->filter(function($book) use ($preferredPeerDifficulty) {
                            return $book['peer_difficulty'] !== null && $book['peer_difficulty'] !== $preferredPeerDifficulty;
                        });

                        // pick matching first, then unrated, then anything else
                        if ($matching->isNotEmpty()) {
                            $selectedBookData = $matching->shuffle()->first();
                        } elseif ($unrated->isNotEmpty()) {
                            $selectedBookData = $unrated->shuffle()->first();
                        } elseif ($other->isNotEmpty()) {
                            $selectedBookData = $other->shuffle()->first();
                        }

                        if ($selectedBookData) break;
                    }
                }

                if ($selectedBookData) {
                    $assignedToThisStudent[] = $selectedBookData;
                    $alreadyReadIds[] = $selectedBookData['id'];
                    
                    $assignedBookId = $selectedBookData['id'];
                    if (isset($availableStockTracker[$assignedBookId])) {
                        $availableStockTracker[$assignedBookId]--;
                        if ($availableStockTracker[$assignedBookId] <= 0) {
                            $unavailableBookIds[] = $assignedBookId;
                        }
                    }
                } else {
                    break; 
                }
            }

            if (!empty($assignedToThisStudent)) {
                $booksToAssign[$student->id] = $assignedToThisStudent;
            }
        }

        $selectedIds = collect($booksToAssign)->flatten(1)->pluck('id')->unique()->toArray();
        $selectedModels = Book::with('genres')->whereIn('id', $selectedIds)->get()->keyBy('id');

        foreach ($students as $student) {
            if (isset($booksToAssign[$student->id]) && !empty($booksToAssign[$student->id])) {
                
                // mark currently reading book as completed
                $currentBooks = $student->books()->wherePivot('status', 'reading')->get();
                foreach ($currentBooks as $cb) {
                    $student->books()->updateExistingPivot($cb->id, ['status' => 'completed']);
                }

                $attachData = [];
                $genreSyncData = [];

                foreach ($booksToAssign[$student->id] as $selectedBookData) {
                    $bookModel = $selectedModels->get($selectedBookData['id']);
                    if (!$bookModel) continue;

                    // assign new book as reading
                    $attachData[$bookModel->id] = [
                        'status' => 'reading',
                        'school_id' => $student->school_id
                    ];

                    DB::table('student_reading_lists')
                        ->where('student_id', $student->id)
                        ->where('book_id', $bookModel->id)
                        ->delete();

                    // add genres with schoolid to pivot table to track preferred genres
                    if ($bookModel->genres->isNotEmpty()) {
                        foreach ($bookModel->genres->pluck('id') as $genreId) {
                            $genreSyncData[$genreId] = ['school_id' => $student->school_id];
                        }
                    }
                }
                
                if (!empty($attachData)) {
                    $student->books()->attach($attachData);
                }

                if (!empty($genreSyncData)) {
                    $student->preferredGenres()->syncWithoutDetaching($genreSyncData);
                }
            }
        }

        // mark generate all as used for this week so the button locks until next week
        $this->markGenerateAllAsUsed($classroom->id);
        
        return back()->with('success', 'Books have been assigned to all students!');
    }

    // Save weekly log?
    public function saveWeeklyLog(Request $request, Classroom $classroom)
    {
        // validate inputs
        $request->validate([
            'weekly_topic' => 'nullable|string|max:255',
            'learning_objective' => 'nullable|string|max:255',
        ]);

        return back()->with('success', 'Weekly objectives saved!');
    }
    // Manually assign a book to a student
    public function assignBook(Request $request, Classroom $classroom, Student $student)
    {   
        // validate book ids
        $request->validate([
            'book_ids'   => 'required|array',
            'book_ids.*' => 'exists:books,id'
        ]);
        // check if the book is banned for this school
        $bannedBooks = DB::table('book_school_ban')
            ->where('school_id', $student->school_id)
            ->whereIn('book_id', $request->book_ids)
            ->exists();

        if ($bannedBooks) {
            return back()->with('error', 'Cannot assign banned or restricted books.');
        }

        $alreadyReadBookIds = $student->books()->whereIn('books.id', $request->book_ids)->pluck('books.id')->toArray();
        $newBookIds = array_diff($request->book_ids, $alreadyReadBookIds);

        if (empty($newBookIds)) {
            return back()->with('error', 'All selected books have already been read or are currently being read by ' . $student->first_name . '.');
        }

        // get students recent difficulty feedback
        $recentReviews = DB::table('book_reviews')
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $difficultyBias = $this->calculateDifficultyBias($recentReviews);
        $preferredPeerDifficulty = $this->mapBiasToPeerDifficulty($difficultyBias);

        // get peer difficulty for the books being assigned
        $bookDifficultyMap = $this->buildBookDifficultyMap($newBookIds);

        // check the colour of the books being assigned vs students recommended colour
        $studentColour = $this->getOxfordColour($student->level);
        $colourBands = [
            'Light Purple', 'Pink', 'Red', 'Yellow', 'Light Blue', 'Green', 'Orange', 
            'Turquoise', 'Purple', 'Gold', 'White', 'Lime', 'Lime+', 'Grey', 'Dark Blue', 'Dark Red'
        ];
        $studentColourIndex = array_search($studentColour, $colourBands);
        if ($studentColourIndex === false) $studentColourIndex = 1;

        // recommended colour change based on the bias
        $recommendedColourIndex = $studentColourIndex;
        if ($difficultyBias < 0 && $studentColourIndex > 1) {
            $recommendedColourIndex = $studentColourIndex - 1;
        } elseif ($difficultyBias > 0 && $studentColourIndex < count($colourBands) - 1) {
            $recommendedColourIndex = $studentColourIndex + 1;
        }

        // mark currently reading book as completed
        $currentBooks = $student->books()->wherePivot('status', 'reading')->get();
        foreach ($currentBooks as $cb) {
            $student->books()->updateExistingPivot($cb->id, ['status' => 'completed']);
        }

        $books = Book::with(['genres', 'phonics'])->whereIn('id', $newBookIds)->get();

        $attachData = [];
        $genreSyncData = [];
        $mismatchWarning = false;
        $peerMismatchWarning = false;

        // assign new book
        foreach ($books as $book) {
            $attachData[$book->id] = [
                'status' => 'reading',
                'school_id' => $student->school_id
            ];

            // check if the books colour matches the recommended difficulty
            $bookColourIndex = array_search($book->ort_colour, $colourBands);
            if ($bookColourIndex !== false && $recommendedColourIndex !== $studentColourIndex) {
                if ($difficultyBias < 0 && $bookColourIndex > $recommendedColourIndex) {
                    $mismatchWarning = true; 
                } elseif ($difficultyBias > 0 && $bookColourIndex < $recommendedColourIndex) {
                    $mismatchWarning = true; 
                }
            }

            // check if the books peer reviewd difficulty conflicts with student preference
            $peerDifficulty = $bookDifficultyMap[$book->id] ?? null;
            if ($peerDifficulty !== null && $preferredPeerDifficulty !== null) {
                // student needs asier and book is rated hard by peers
                if ($difficultyBias < 0 && $peerDifficulty === 'hard') {
                    $peerMismatchWarning = true;
                }
                // student needs harder and book is rated easy by peers
                elseif ($difficultyBias > 0 && $peerDifficulty === 'easy') {
                    $peerMismatchWarning = true;
                }
            }

            // add genres with schoolid to pivot table to track preferred genres
            foreach ($book->genres->pluck('id') as $genreId) {
                $genreSyncData[$genreId] = ['school_id' => $student->school_id];
            }
        }

        $student->books()->attach($attachData);
        if (!empty($genreSyncData)) {
            $student->preferredGenres()->syncWithoutDetaching($genreSyncData);
        }

        DB::table('student_reading_lists')
            ->where('student_id', $student->id)
            ->whereIn('book_id', $newBookIds)
            ->delete();
        
        $count = count($newBookIds);
        $bookWord = $count === 1 ? 'Book' : 'Books';

        return back()->with('success', $count . ' ' . $bookWord . ' successfully assigned to ' . $student->first_name);
    }

    // Calculcate difficulty bias from recent reviews
    private function calculateDifficultyBias($reviews)
    {
        if ($reviews->isEmpty()) return 0;

        $score = 0;
        // loop through reviews
        foreach ($reviews as $review) {
            // hard = books too hard, student wants easier - negative bias
            // easy = books too easy, student wants harder - positive bias
            $score += match ($review->difficulty) {
                'easy' => 1,   
                'hard' => -1,  
                default => 0,  
            };
        }

        $average = $score / $reviews->count();

        // strong bias if more than half lean a way
        if ($average <= -0.5) return -1; 
        if ($average >= 0.5) return 1;   
        return 0; 
    }

    // Map bias to peer difficulty
    // bias < 0 = student wants easier
    // bias = 0 = student wants okay
    // bias > 0 = student wants harder
    private function mapBiasToPeerDifficulty(int $bias): string
    {
        return match ($bias) {
            -1 => 'easy',
            1 => 'hard',
            default => 'okay',
        };
    }

    // build map of book_id = easy/okay/hard on aggregated student reviews
    // only books within minimum reviews, others are null
    private function buildBookDifficultyMap(?array $bookIds = null): array
    {
        // need at least 3 reviews
        $minReviews = 3;

        $query = DB::table('book_reviews')
            ->select('book_id', 'difficulty', DB::raw('count(*) as count'))
            ->groupBy('book_id', 'difficulty');

        // skip cache when looking up specific books since theyre usually new requests
        if ($bookIds !== null) {
            return $this->computeBookDifficultyMap($bookIds);
        }


        $rows = $query->get();

        // group results by book id, counter each difficukty
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->book_id][$row->difficulty] = $row->count;
        }

        // classify each book based on weighted avg
        $map = [];
        foreach ($grouped as $bookId => $counts) {
            $easy = $counts['easy'] ?? 0;
            $okay = $counts['okay'] ?? 0;
            $hard = $counts['hard'] ?? 0;
            $total = $easy + $okay + $hard;

            // skip books with few reviews
            if ($total < $minReviews) continue;

            // weight easy = 1, okay = 2, hard = 3 then take the average
            $score = (($easy * 1) + ($okay * 2) + ($hard * 3)) / $total;

            // get equal thirds of 1 to 3 range
            if ($score < 1.67) {
                $map[$bookId] = 'easy';
            } elseif ($score < 2.34) {
                $map[$bookId] = 'okay';
            } else {
                $map[$bookId] = 'hard';
            }
        }

        return $map;
    }

    // Compute book difficulty map, separated out so the cache wrapper stays clean
    private function computeBookDifficultyMap(?array $bookIds = null): array
    {
        // need at least 3 reviews
        $minReviews = 3;

        $query = DB::table('book_reviews')
            ->select('book_id', 'difficulty', DB::raw('count(*) as count'))
            ->groupBy('book_id', 'difficulty');
        
        // filter by ids if subset is requested, used in assignbook for efficiency
        if ($bookIds !== null) {
            if (empty($bookIds)) return [];
            $query->whereIn('book_id', $bookIds);
        }

        $rows = $query->get();

        // group results by book id, counter each difficukty
        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->book_id][$row->difficulty] = $row->count;
        }

        // classify each book based on weighted avg
        $map = [];
        foreach ($grouped as $bookId => $counts) {
            $easy = $counts['easy'] ?? 0;
            $okay = $counts['okay'] ?? 0;
            $hard = $counts['hard'] ?? 0;
            $total = $easy + $okay + $hard;

            // skip books with few reviews
            if ($total < $minReviews) continue;

            // weight easy = 1, okay = 2, hard = 3 then take the average
            $score = (($easy * 1) + ($okay * 2) + ($hard * 3)) / $total;

            // get equal thirds of 1 to 3 range
            if ($score < 1.67) {
                $map[$bookId] = 'easy';
            } elseif ($score < 2.34) {
                $map[$bookId] = 'okay';
            } else {
                $map[$bookId] = 'hard';
            }
        }

        return $map;
    }

    // check if generateall has been used for this classroom this week - checks both cache flag and bookstudent records JUST INCASE
    private function hasGenerateAllRunThisWeek(int $classroomId, array $studentIds): bool
    {
        // check cache flag first since its quicker
        if (Cache::has($this->generateAllCacheKey($classroomId))) {
            return true;
        }

        // check if any books were assigned to multiple students this week
        // if more than half the class got a book this week, it is already generated this week
        if (empty($studentIds)) return false;

        $startOfWeek = now()->startOfWeek();
        $assignedThisWeek = DB::table('book_student')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'reading')
            ->where('created_at', '>=', $startOfWeek)
            ->distinct('student_id')
            ->count('student_id');

        // assume generate all was used if more than half the class got a new book this week
        return $assignedThisWeek >= ceil(count($studentIds) / 2);
    }

    // mark generateall has been used
    private function markGenerateAllAsUsed(int $classroomId): void
    {
        // store flag until the end of current week so the lock auto resets
        Cache::put(
            $this->generateAllCacheKey($classroomId),
            true,
            now()->endOfWeek()
        );
    }

    // build cache key for the classroom generatea all lock
    private function generateAllCacheKey(int $classroomId): string
    {
        return "classroom:{$classroomId}:generate_all_used:" . now()->startOfWeek()->format('Y_W');
    }

    // Convert ort to colour
    private function getOxfordColour($level) {
        return match((int)$level) {
            0 => 'Light Purple',
            1 => 'Pink',
            2 => 'Red',
            3 => 'Yellow', 
            4 => 'Light Blue',
            5 => 'Green',
            6 => 'Orange',
            7 => 'Turquoise', 
            8 => 'Purple',
            9 => 'Gold',
            10 => 'White', 
            11 => 'Lime', 
            12 => 'Lime+',
            13, 14 => 'Grey',
            15, 16 => 'Dark Blue', 
            17, 18, 19, 20 => 'Dark Red',
            default => 'Dark Red',
        };
    }
}