<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Phonic;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExploreController extends Controller {
    
    public function index(Request $request) {
        // book query
        $query = Book::query();
        $query->with('genres');

        // Hide banned books from users school
        if (Auth::check() && Auth::user()->school_id) {
            $schoolId = Auth::user()->school_id;
            
            // only hide if the book is banned with option 2
            $query->whereDoesntHave('bannedBySchools', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId)->where('ban_type', 'hide');
            });
        }

        // search request title
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // search request author
        if ($request->filled('author')) {
            $query->where('author', 'like', '%' . $request->author . '%');
        }

        // get minimum level
        if ($request->filled('level_min')) {
            $query->where('ort_level', '>=', $request->level_min);
        }

        // get maximum level
        if ($request->filled('level_max')) {
            $query->where('ort_level', '<=', $request->level_max);
        }

        // get genres
        if ($request->filled('genre')) {
            $genreSlugs = (array) $request->genre;
            
            // instantly get genre ids
            $genreIds = Genre::whereIn('slug', $genreSlugs)->pluck('id');
            
            // instantly get matching book ids from the pivot table
            $bookIds = DB::table('book_genre')->whereIn('genre_id', $genreIds)->pluck('book_id');
            
            // filter query using primary keys (fast)
            $query->whereIn('id', $bookIds);
        }

        // get phonics
        if ($request->filled('phonic')) {
            
            $phonicIds = (array) $request->phonic; 

            // get matching book ids from the pivot table
            $bookIds = DB::table('book_phonic')->whereIn('phonic_id', $phonicIds)->pluck('book_id');
            
            // filter query
            $query->whereIn('id', $bookIds);
        }

        // online books toggle
        if ($request->has('readable') && $request->readable == '1') {
            $query->where('ol_key', 'not like', 'NO_OL_%');
        }

        // sort by lowest level first
        $sort = $request->get('sort', 'level-low');

        // sort by filters
        if ($sort === 'a-z') {
            // title
            $query->orderBy('title', 'asc');
        } elseif ($sort === 'level-low') {
            // level ascending
            $query->orderBy('ort_level', 'asc');
        } elseif ($sort === 'level-high') {
            // level descending
            $query->orderBy('ort_level', 'desc');
        } elseif ($sort === 'author-a-z') {
            // authors a - z
            $query->orderBy('author', 'asc');
        } elseif ($sort === 'author-z-a') {
            // authors z - a
            $query->orderBy('author', 'desc');
        } elseif ($sort === 'custom') {
            // custom created books
            $query->orderByRaw("CASE WHEN ol_key LIKE 'NO_OL_CUSTOM_%' THEN 0 ELSE 1 END")->orderBy('created_at', 'desc');
        } else {
            $query->latest();
        }

        // paginate each page w/ 28
        $books = $query->paginate(28)->withQueryString();
        
        // fetch genres and phonics (for sidebar)
        $genres = Genre::orderBy('name')->get();
        // ordered phonics alphabetically
        $phonics = Phonic::orderBy('sound')->get(); 

        // favourite book ids
        $favouritedBookIds = [];
        if (Auth::check() && Auth::user()->student) {
            $favouritedBookIds = DB::table('student_favourite_books')
                ->where('student_id', Auth::user()->student->id)
                ->pluck('book_id')
                ->toArray();
        }

        return view('explore', compact('books', 'genres', 'phonics', 'favouritedBookIds'));
    }

    // show individual book page
    public function show($id, Request $request)
    {
        $book = Book::with(['genres', 'phonics', 'reviews.student.user'])->findOrFail($id);
        
        $user = Auth::user();
        $schoolId = $user?->school_id ?? $user?->student?->school_id ?? $user?->teacher?->school_id ?? null;
        

        // edit book for TEACHERS only and ADDED books and ADMINS
        $editMode = $request->has('edit') && $user && (
            ($user->isTeacher() && str_starts_with($book->ol_key, 'NO_OL_CUSTOM_')) || $user->isAdmin()
        );

        $totalStock = 0;
        $readingCount = 0;
        $requestedCount = 0;
        $availableStock = 0;
        
        if ($schoolId) {
            // total stock for book in this school
            $totalStock = DB::table('book_school_stocks')
                ->where('school_id', $schoolId)
                ->where('book_id', $book->id)
                ->value('stock') ?? 0;
                
            // get amount of students currently reading book in school
            $readingCount = DB::table('book_student')
                ->where('school_id', $schoolId)
                ->where('book_id', $book->id)
                ->where('status', 'reading')
                ->count();
                
            // get amount of students who have requested this book
            $requestedCount = DB::table('student_reading_lists')
                ->where('school_id', $schoolId)
                ->where('book_id', $book->id)
                ->where('status', 'pending')
                ->count();
                
            // available stock calculation
            $availableStock = max(0, $totalStock - $readingCount);
        }

        // set the bantype to null as default
        $banType = null;

        if ($schoolId) {
            // query pivot table, checking for book id and school id and get it
            $banRecord = DB::table('book_school_ban')
                ->where('book_id', $book->id)
                ->where('school_id', $schoolId)
                ->first();

            if ($banRecord) {
                // assign ban type, restrict/hide
                $banType = $banRecord->ban_type;

                // only allow headteachers/teachers/admins
                if ($banType === 'hide' && $user->role !== 'headteacher' && !$user->isAdmin() && $user->role !== 'teacher') {
                    abort(403, 'This book has been hidden by school administrators');
                }
            }
        }

        $reviews = $book->reviews;

        // get the sort parameter and set default to top
        $sort = $request->query('sort', 'top');

        // sort reviews based on the filters
        switch ($sort) {
            case 'recent':
                $reviews = $reviews->sortByDesc('created_at')->values();
                break;

            case 'classroom':
                if (Auth::check() && $user->student) {
                    $classroomID = $user->student->classroom_id;
                    $reviews = $reviews->filter(function ($review) use ($classroomID) {
                        return $review->student && $review->student->classroom_id === $classroomID;
                    })->sortByDesc('created_at')->values();
                }
                break;

            case 'top':
            default:
                $reviews = $reviews->sortByDesc('upvotes')->values();
                break;
        }
        
        // upvoted review ids
        $upvotedReviewIds = [];
        if (Auth::check()) {
            $upvotedReviewIds = $user
                ->upvotedReviews()
                ->whereIn('book_review_id', $reviews->pluck('id'))
                ->pluck('book_review_id')
                ->toArray();
        }

        $currentSort = $sort;

        return view('book', compact('book', 'reviews', 'upvotedReviewIds', 'currentSort', 'banType', 'totalStock', 'availableStock', 'readingCount', 'requestedCount', 'editMode'));
    }

    // Create book
    public function addBook(Request $request)
    {   
        // validate inputs
        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'author'        => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s.]+$/'], // letters and spaces only AND full stops
            'ort_level'     => 'required|integer|min:0|max:20',
            'description'   => 'nullable|string',
            'cover_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB max
            'new_phonics'   => 'nullable|array', // phonics array
            'new_phonics.*' => 'string|alpha|max:50', // letters only for phonics
        ], [
            // error messages
            'author.regex'        => 'Author names can only contain letters and spaces.',
            'new_phonics.*.alpha' => 'Phonics may only contain letters.',
        ]);

        // trim title and author
        $cleanTitle = trim($validated['title']);
        $cleanAuthor = trim($validated['author']);

        // check if book already exists, match BOTH title and author
        // authors can create multiple books
        $bookExists = Book::whereRaw('LOWER(title) = ?', [strtolower($cleanTitle)])->whereRaw('LOWER(author) = ?', [strtolower($cleanAuthor)])->exists();

        if ($bookExists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['duplicate' => 'A book with this title and author already exists.']); // if title and author is the same
        }

        // map level to colour
        $ortColours = [
            0 => 'Light Purple', 1 => 'Pink',      2 => 'Red',       3 => 'Yellow',
            4 => 'Light Blue',   5 => 'Green',      6 => 'Orange',    7 => 'Turquoise',
            8 => 'Purple',       9 => 'Gold',       10 => 'White',    11 => 'Lime',
            12 => 'Lime+',       13 => 'Grey',      14 => 'Grey',     15 => 'Dark Blue',
            16 => 'Dark Blue',   17 => 'Dark Red',  18 => 'Dark Red', 19 => 'Dark Red',
            20 => 'Dark Red',
        ];
        $ortColour = $ortColours[$validated['ort_level']] ?? 'Unknown';

        // cover image
        $coverId = null;
        if ($request->hasFile('cover_image')) {
            // custom iamge
            $path = $request->file('cover_image')->store('covers', 'public');
            $coverId = 'LOCAL_' . $path; 

        } else {
            $similarBook = null;

            // get exact match first
            $similarBook = Book::whereRaw('LOWER(title) = ?', [strtolower($cleanTitle)])->whereNotNull('cover_id')->where('cover_id', 'NOT LIKE', 'PLACEHOLDER_%')->first();

            // if no exact match, try a different search (for longer words)
            if (!$similarBook) {
                $titleWords = explode(' ', preg_replace('/[^a-z0-9 ]/i', '', strtolower($cleanTitle)));
                // filter out small words like a, of, the
                $significantWords = array_filter($titleWords, fn($w) => strlen($w) > 3);

                // run query if there are actually significant words left
                if (!empty($significantWords)) {
                    $query = Book::whereNotNull('cover_id')->where('cover_id', 'NOT LIKE', 'PLACEHOLDER_%');
                    
                    foreach ($significantWords as $word) {
                        $query->where('title', 'LIKE', '%' . $word . '%');
                    }
                    
                    $similarBook = $query->first();
                }
            }

            if ($similarBook) {
                // found a match
                $coverId = $similarBook->cover_id;

            } else {
                // fallback to google books api to fetch cover
                try {
                    $googleResponse = \Illuminate\Support\Facades\Http::timeout(5)->get("https://www.googleapis.com/books/v1/volumes", [
                        'q' => 'intitle:' . $cleanTitle . ' inauthor:' . $cleanAuthor,
                        'maxResults' => 1,
                        'key' => env('GOOGLE_BOOKS_API_KEY')
                    ]);
                    if ($googleResponse->successful() && !empty($googleResponse->json('items'))) {
                        $item = $googleResponse->json('items')[0];
                        if (!empty($item['volumeInfo']['imageLinks']['thumbnail'])) {
                            $coverId = $item['id']; 
                        }
                    }
                } catch (\Exception $e) {
                    
                }
            }

            // create placeholder if the local search and api fails
            if (!$coverId) {
                $rainbowHex = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#6366f1', '#8b5cf6'];
                $randomColour = $rainbowHex[array_rand($rainbowHex)];
                $coverId = 'PLACEHOLDER_' . $randomColour;
            }
        }

        // create book
        $newBook = Book::create([
            'ol_key'      => 'NO_OL_CUSTOM_' . \Illuminate\Support\Str::random(10), 
            'title'       => $validated['title'],
            'author'      => $validated['author'],
            'cover_id'    => $coverId,
            'ort_level'   => $validated['ort_level'],
            'ort_colour'  => $ortColour,
            'description' => $validated['description'],
        ]);

        // attach phonics if any were submitted
        if (!empty($validated['new_phonics'])) {
            $phonicIds = [];
            
            foreach ($validated['new_phonics'] as $sound) {
                $cleanSound = trim($sound);
                
                if ($cleanSound !== '') {
                    // find existing phonic or create a new one to prevent duplicates
                    $phonic = Phonic::firstOrCreate(['sound' => $cleanSound]);
                    $phonicIds[] = $phonic->id;
                }
            }
            
            if (!empty($phonicIds)) {
                $newBook->phonics()->sync($phonicIds);
            }
        }

        return redirect()->back()->with('success', 'Book added successfully!');
    }

    // delete book
    public function deleteBook(Book $book)
    {
        // check if teacher/admin is deleting
        if (!auth()->user()?->isTeacher() && !auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorised action');
        }

        // only delete custom created books
        if (!str_starts_with($book->ol_key, 'NO_OL_CUSTOM_')) {
            abort(403, 'You can only delete manually created books');
        }

        // delete from storage if it has a locally uploaded cover
        if ($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_')) {
            $path = str_replace('LOCAL_', '', $book->cover_id);
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }

        // delete
        $book->delete();

        return redirect()->back()->with('success', 'Custom book removed successfully!');
    }

    // Add book to reading list
    public function requestBook(Request $request, Book $book)
    {
        $user = Auth::user();

        // ensure the user is a student
        if (!$user || !$user->student) {
            return back()->with('error', 'Only students can request to read books.');
        }

        $student = $user->student;

        // validate reading level - if book is level 5, student level must be 4, 5, 6
        $studentLevel = (int) $student->level;
        $bookLevel = (int) $book->ort_level;

        // reading level error message
        if (abs($bookLevel - $studentLevel) > 1) {
            return back()->with('error', 'This book is not suitable for your reading level. You need to be a similar reading level');
        }

        // validate stock
        $totalStock = DB::table('book_school_stocks')
            ->where('school_id', $student->school_id)
            ->where('book_id', $book->id)
            ->value('stock') ?? 0;
            
        $readingCount = DB::table('book_student')
            ->where('school_id', $student->school_id)
            ->where('book_id', $book->id)
            ->where('status', 'reading')
            ->count();
            
        // stock message error
        if (($totalStock - $readingCount) <= 0) {
            return back()->with('error', 'This book is currently unavailable');
        }

        // already reading
        $alreadyReading = DB::table('book_student')
            ->where('student_id', $student->id)
            ->where('book_id', $book->id)
            ->where('status', 'reading')
            ->exists();

        if ($alreadyReading) {
            return back()->with('error', 'You are already reading this book!');
        }

        // already read
        $alreadyRead = DB::table('book_student')
            ->where('student_id', $student->id)
            ->where('book_id', $book->id)
            ->where('status', 'completed')
            ->exists();
        
        if($alreadyRead) {
            return back()->with('error', 'You have already read this book!');
        }

        // already requested
        $alreadyRequested = DB::table('student_reading_lists')
            ->where('student_id', $student->id)
            ->where('book_id', $book->id)
            ->where('status', 'pending')
            ->exists();

        if ($alreadyRequested) {
            return back()->with('error', 'You have already requested this book. Please wait for your teacher to approve it.');
        }

        // reading list limit
        $currentListCount = DB::table('student_reading_lists')
            ->where('student_id', $student->id)
            ->whereIn('status', ['pending', 'reading']) // active - pending/reading
            ->count();

        // reading list error
        if ($currentListCount >= 5) {
            return back()->with('error', 'Your reading list is full! You can only have 5 active books on your list at one time. Try removing a different book in your dashboard.');
        }

        // Insert into db
        DB::transaction(function () use ($student, $book) {
            // add to reading list
            DB::table('student_reading_lists')->insert([
                'school_id'    => $student->school_id,
                'classroom_id' => $student->classroom_id,
                'student_id'   => $student->id,
                'book_id'      => $book->id,
                'status'       => 'pending',
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            // announcement for teacher
            if ($student->classroom_id) {
                DB::table('announcements')->insert([
                    'school_id'    => $student->school_id,
                    'classroom_id' => $student->classroom_id,
                    'student_id'   => $student->id, 
                    'message'      => "Reading request: {$student->first_name} {$student->last_name} has requested to read '{$book->title}'.",
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
            
        });

        return back()->with('success', 'Book requested successfully! Your teacher has been notified.');
    }

    // Update book stock
    public function updateStock(Request $request, Book $book)
    {
        $user = Auth::user();
        $schoolId = $user->school_id ?? $user->teacher->school_id ?? null;

        if (!$schoolId) {
            return back()->with('error', 'No school assigned to your account');
        }

        // increase/decrease
        $action = $request->input('action');

        // get current stock
        $stockRecord = DB::table('book_school_stocks')
            ->where('book_id', $book->id)
            ->where('school_id', $schoolId)
            ->first();

        $currentStock = $stockRecord ? $stockRecord->stock : 0;
        $newStock = $currentStock;

        // if increase
        if ($action === 'increase') {
            $newStock = $currentStock + 1;
        } elseif ($action === 'decrease') {
            // if decrease, check how many are currently reading it
            $readingCount = DB::table('book_student')
                ->where('school_id', $schoolId)
                ->where('book_id', $book->id)
                ->where('status', 'reading')
                ->count();

            // decrease if stock is greater than currently reading
            if ($currentStock > $readingCount) {
                $newStock = $currentStock - 1;
            } else {
                return back()->withErrors(['stock' => 'Cannot decrease stock below the number of students currently reading this book']);
            }
        }

        // update/insert stock
        if ($stockRecord) {
            DB::table('book_school_stocks')
                ->where('id', $stockRecord->id)
                ->update(['stock' => $newStock, 'updated_at' => now()]);
        } else {
            DB::table('book_school_stocks')->insert([
                'book_id' => $book->id,
                'school_id' => $schoolId,
                'stock' => $newStock,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back();
    }

    // toggle favourite book
    public function toggleFavourite(Book $book)
    {        
        $user = Auth::user();

        if (!$user || !$user->student) {
            return back()->with('error', 'Only students can favourite books!');
        }

        $student = $user->student;

        // check if already favourited
        $existingFavourite = DB::table('student_favourite_books')
            ->where('student_id', $student->id)
            ->where('book_id', $book->id)
            ->first();

        // if already favourited
        if ($existingFavourite) {
            //  removed from favourite
            DB::table('student_favourite_books')
                ->where('id', $existingFavourite->id)
                ->delete();

            return back()->with('success', 'Book removed from favourites!');
        } else {
            // insert into db
            DB::table('student_favourite_books')->insert([
                'school_id'    => $student->school_id,
                'classroom_id' => $student->classroom_id,
                'student_id'   => $student->id,
                'book_id'      => $book->id,
                'created_at'   => now(),
                'updated_at'   => now(),
            ]);

            return back()->with('success', 'Book added to favourites!');
        }
    }

    // Update book
    public function updateBook(Request $request, Book $book)
    {
        $user = auth()->user();

        if (!$user?->isTeacher() && !$user?->isAdmin()) {
            abort(403, 'Only teachers and admins can edit books');
        }

        // teachers can edit added books, admins can edit anything
        if ($user->isTeacher() && !$user->isAdmin() && !str_starts_with($book->ol_key, 'NO_OL_CUSTOM_')) {
            abort(403, 'You can only edit manually created books');
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'author'        => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s.]+$/'],
            'ort_level'     => 'required|integer|min:0|max:20',
            'description'   => 'nullable|string',
            'cover_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_cover'  => 'nullable',
            'new_phonics'   => 'nullable|array',
            'new_phonics.*' => 'string|alpha|max:50',
        ], [
            'author.regex'        => 'Author names can only contain letters, spaces and full stops.',
            'new_phonics.*.alpha' => 'Phonics may only contain letters.',
        ]);

        // map level to colour
        $ortColours = [
            0 => 'Light Purple', 1 => 'Pink',      2 => 'Red',       3 => 'Yellow',
            4 => 'Light Blue',   5 => 'Green',      6 => 'Orange',    7 => 'Turquoise',
            8 => 'Purple',       9 => 'Gold',       10 => 'White',    11 => 'Lime',
            12 => 'Lime+',       13 => 'Grey',      14 => 'Grey',     15 => 'Dark Blue',
            16 => 'Dark Blue',   17 => 'Dark Red',  18 => 'Dark Red', 19 => 'Dark Red',
            20 => 'Dark Red',
        ];

        // cover removal
        if ($request->has('remove_cover')) {
            if ($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_')) {
                $path = str_replace('LOCAL_', '', $book->cover_id);
                Storage::disk('public')->delete($path);
            }
            $rainbowHex = ['#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#6366f1', '#8b5cf6'];
            $book->cover_id = 'PLACEHOLDER_' . $rainbowHex[array_rand($rainbowHex)];
        }

        // cover upload
        if ($request->hasFile('cover_image')) {
            if ($book->cover_id && str_starts_with($book->cover_id, 'LOCAL_')) {
                $path = str_replace('LOCAL_', '', $book->cover_id);
                Storage::disk('public')->delete($path);
            }
            $path = $request->file('cover_image')->store('covers', 'public');
            $book->cover_id = 'LOCAL_' . $path;
        }

        // update book fields
        $book->update([
            'title'       => $validated['title'],
            'author'      => $validated['author'],
            'ort_level'   => $validated['ort_level'],
            'ort_colour'  => $ortColours[$validated['ort_level']] ?? 'Unknown',
            'description' => $validated['description'],
            'cover_id'    => $book->cover_id,
        ]);

        // sync phonics
        if (!empty($validated['new_phonics'])) {
            $phonicIds = [];
            foreach ($validated['new_phonics'] as $sound) {
                $cleanSound = trim($sound);
                if ($cleanSound !== '') {
                    $phonic = Phonic::firstOrCreate(['sound' => $cleanSound]);
                    $phonicIds[] = $phonic->id;
                }
            }
            $book->phonics()->sync($phonicIds);
        } else {
            $book->phonics()->detach();
        }

        return redirect()->route('books.show', $book->id)->with('success', 'Book updated successfully!');
    }
}