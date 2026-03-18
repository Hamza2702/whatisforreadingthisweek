<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classroom; 
use App\Models\Student;
use App\Models\Book;  

class ReadingController extends Controller
{
    // Show page and generate choices for each student
    public function generateList(Classroom $classroom)
    {   
        $yearGroups = []; 
        // get students and genres
        $students = $classroom->students()->with(['user', 'preferredGenres'])->get();

        // colours
        $colourBands = [
            'Light Purple', 'Pink', 'Red', 'Yellow', 'Light Blue', 'Green', 'Orange', 
            'Turquoise', 'Purple', 'Gold', 'White', 'Lime', 'Lime+', 'Grey', 'Dark Blue', 'Dark Red'
        ];

        // loop through students
        foreach ($students as $student) {
            // convert level to oxford colour
            $student->ort_colour = $this->getOxfordColour($student->level);
            
            // store recommended books
            $recommended = collect();
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

            // fetch books based on filters
            $fetchBooks = function($targetColour, $isLiked, $limit) use ($likedGenresIds, &$recommended) {
                if ($limit <= 0 || !$targetColour) return; // skip

                // query
                $query = Book::with(['genres', 'phonics'])
                    ->where('ort_colour', $targetColour)
                    ->whereNotIn('id', $recommended->pluck('id')->toArray());

                // apply genre preference filtering
                if (!empty($likedGenresIds)) {
                    if ($isLiked) {
                        // only liked genres
                        $query->whereHas('genres', fn($q) => $q->whereIn('genres.id', $likedGenresIds));
                    } else {
                        // exclude liked genres
                        $query->whereDoesntHave('genres', fn($q) => $q->whereIn('genres.id', $likedGenresIds));
                    }
                }

                // fetch random books
                $books = $query->inRandomOrder()->take($limit)->get();
                // add to recommended list
                $recommended = $recommended->concat($books);
            };

            // fetch books based on priority order
            $fetchBooks($belowColour, true, $limits['below_liked']);   
            $fetchBooks($sameColour,  true, $limits['same_liked']);     
            $fetchBooks($aboveColour, true, $limits['above_liked']);    
            $fetchBooks($sameColour,  false, $limits['same_unliked']);  
            $fetchBooks($aboveColour, false, $limits['above_unliked']); 

            // if not enough books to fill up to 10 books
            $missingCount = 10 - $recommended->count();
            if ($missingCount > 0) {
                $searchColours = array_filter([$belowColour, $sameColour, $aboveColour]);
                $fillerBooks = Book::with(['genres', 'phonics'])
                    ->whereIn('ort_colour', $searchColours)
                    ->whereNotIn('id', $recommended->pluck('id')->toArray())
                    ->inRandomOrder()->take($missingCount)->get();
                $recommended = $recommended->concat($fillerBooks);
            }

            // add recommended books to student
            $student->recommendedBooks = $recommended;
        }

        return view('teacher.classes.generate-reading-list', compact('classroom', 'students', 'yearGroups'));
    }

    // Generate books for all students
    public function generateAll(Request $request, Classroom $classroom)
    {
        // get students with genres and current books
        $students = $classroom->students()->with(['preferredGenres', 'books'])->get();

        // loop through students
        foreach ($students as $student) {
            // get students colour level
            $ortColour = $this->getOxfordColour($student->level);
            // get liked genres
            $likedGenresIds = $student->preferredGenres->pluck('id')->toArray();
            // get already read book ids
            $alreadyReadIds = $student->books->pluck('id')->toArray();

            // try matching a book (same colour, liked genre and unread)
            $book = Book::where('ort_colour', $ortColour)
                ->whereNotIn('id', $alreadyReadIds)
                ->whereHas('genres', fn($q) => $q->whereIn('genres.id', $likedGenresIds))
                ->inRandomOrder()
                ->first();

            // fall back, any unread book in the same colour
            if (!$book) {
                $book = Book::where('ort_colour', $ortColour)
                    ->whereNotIn('id', $alreadyReadIds)
                    ->inRandomOrder()
                    ->first();
            }

            // assign book if found
            if ($book) {
                // mark currently reading book as completed
                $currentBooks = $student->books()->wherePivot('status', 'reading')->get();
                foreach ($currentBooks as $cb) {
                    $student->books()->updateExistingPivot($cb->id, ['status' => 'completed']);
                }

                // assign new book as reading
                $student->books()->attach($book->id, [
                    'status' => 'reading',
                    'school_id' => $student->school_id 
                ]);
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
        // validate book id
        $request->validate([
            'book_id' => 'required|exists:books,id'
        ]);

        // mark currently reading book as completed
        $currentBooks = $student->books()->wherePivot('status', 'reading')->get();
        foreach ($currentBooks as $book) {
            $student->books()->updateExistingPivot($book->id, ['status' => 'completed']);
        }

        // assign new book
        $student->books()->attach($request->book_id, [
            'status' => 'reading',
            'school_id' => $student->school_id 
        ]);

        return back()->with('success', 'Book manually assigned to ' . $student->first_name);
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
            1 => 'Lime', 
            12 => 'Lime+',
            13, 14 => 'Grey',
            15, 16 => 'Dark Blue', 
            17, 18, 19, 20 => 'Dark Red',
            default => 'Dark Red',
        };
    }
}