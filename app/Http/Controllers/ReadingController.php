<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classroom; 
use App\Models\Student;
use App\Models\Book;  
use Illuminate\Support\Facades\DB;
use App\Models\StudentReadingList;
use App\Models\StudentFavouriteBook;

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

        // load reading lists
        $allReadingListEntries = StudentReadingList::whereIn('student_id', $studentIds)
            ->with(['book.genres', 'book.phonics'])
            ->get()
            ->groupBy('student_id');

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

            // chunk books with id
            $query->with('genres:id')->chunkById(1000, function ($books) use (&$availableBooks) {
                foreach ($books as $book) {
                    $availableBooks[$book->ort_colour][] = [
                        'id' => $book->id,
                        'colour' => $book->ort_colour,
                        'genres' => $book->genres->pluck('id')->toArray()
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

            // array to hold selected book ids for this specific student
            $selectedIdsForStudent = [];

            // fetch books based on filters
            $fetchBooks = function($targetColour, $isLiked, $limit) use ($likedGenresIds, &$selectedIdsForStudent, $availableBooks, $alreadyReadOrReadingIds) {
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

                $picked = $available->shuffle()->take($limit)->pluck('id')->toArray();
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
            
            $student->recommendedBooks = collect($assignedIds)->map(function($id) use ($finalLoadedBooks) {
                return $finalLoadedBooks->get($id);
            })->filter();
        }

        return view('teacher.classes.reading-list', compact('classroom', 'students', 'yearGroups'));
    }

    // Generate books for all students
    public function generateAll(Request $request, Classroom $classroom)
    {
        // get students with genres and current books
        $students = $classroom->students()->with(['preferredGenres', 'books', 'weeklyGoal'])->get();
        if ($students->isEmpty()) return back()->with('success', 'No students in classroom.');

        $schoolId = $students->first()?->school_id;
        $studentIds = $students->pluck('id')->toArray();

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

        // create available books
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

            $query->with('genres:id')->chunkById(1000, function($books) use (&$availableBooks) {
                foreach($books as $book) {
                    $availableBooks[$book->ort_colour][] = [
                        'id' => $book->id,
                        'genres' => $book->genres->pluck('id')->toArray()
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

                if (!empty($availableBooks[$ortColour])) {
                    $availableForStudent = collect($availableBooks[$ortColour])->reject(function($book) use ($alreadyReadIds, $unavailableBookIds) {
                        return in_array($book['id'], $alreadyReadIds) || in_array($book['id'], $unavailableBookIds);
                    });

                    if ($availableForStudent->isEmpty()) break; 

                    $preferredBooks = $availableForStudent->filter(function($book) use ($likedGenresIds) {
                        return !empty(array_intersect($book['genres'], $likedGenresIds));
                    });

                    $selectedBookData = $preferredBooks->isNotEmpty() 
                        ? $preferredBooks->shuffle()->first() 
                        : $availableForStudent->shuffle()->first();
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

        // mark currently reading book as completed
        $currentBooks = $student->books()->wherePivot('status', 'reading')->get();
        foreach ($currentBooks as $cb) {
            $student->books()->updateExistingPivot($cb->id, ['status' => 'completed']);
        }

        $books = Book::with(['genres', 'phonics'])->whereIn('id', $newBookIds)->get();

        $attachData = [];
        $genreSyncData = [];

        // assign new book
        foreach ($books as $book) {
            $attachData[$book->id] = [
                'status' => 'reading',
                'school_id' => $student->school_id
            ];

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