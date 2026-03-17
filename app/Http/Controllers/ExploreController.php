<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Genre;
use App\Models\Phonic;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ExploreController extends Controller {
    
    public function index(Request $request) {
        // book query
        $query = Book::query();
        $query->with('genres');

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

        return view('explore', compact('books', 'genres', 'phonics'));
    }

    // show individual book page
    public function show($id) {
        // return book, findorfail = (find or fails loading page if user types fake id)
        $book = Book::with(['genres', 'phonics'])->findOrFail($id);
        
        return view('book', compact('book'));
    }

    // Create book
    public function addBook(Request $request)
    {   
        // validate inputs
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'author'      => 'required|string|max:255',
            'ort_level'   => 'required|integer|min:0|max:20',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB max
        ]);

        // trim title and author
        $cleanTitle = trim($validated['title']);
        $cleanAuthor = trim($validated['author']);

        // check if book already exists, match title and author
        $bookExists = Book::whereRaw('LOWER(title) = ?', [strtolower($cleanTitle)])->whereRaw('LOWER(author) = ?', [strtolower($cleanAuthor)])->exists();

        if ($bookExists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'A book with this title and author already exists in the database.');
        }

        // map level to colour
        $ortColours = [
            0 => "#6A5ACD",  1 => "#FF69B4",  2 => "#FF0000",  3 => "#FFFF00",
            4 => "#67C5F4",  5 => "#00FA36",  6 => "#FF892E",  7 => "#40e0d0",
            8 => "#6A1B9A",  9 => "#D4AF37",  10 => "#FFFFFF", 11 => "#bfff00",
            12 => "#AED581", 13 => "#9E9E9E", 14 => "#9E9E9E", 15 => "#0D47A1",
            16 => "#0D47A1", 17 => "#B71C1C", 18 => "#B71C1C", 19 => "#B71C1C",
            20 => "#B71C1C",
        ];
        $ortColour = $ortColours[$validated['ort_level']] ?? '#FFFFFF';

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
                $randomColor = $rainbowHex[array_rand($rainbowHex)];
                $coverId = 'PLACEHOLDER_' . $randomColor;
            }
        }

        // create book
        Book::create([
            'ol_key'      => 'NO_OL_CUSTOM_' . \Illuminate\Support\Str::random(10), 
            'title'       => $validated['title'],
            'author'      => $validated['author'],
            'cover_id'    => $coverId,
            'ort_level'   => $validated['ort_level'],
            'ort_colour'  => $ortColour,
            'description' => $validated['description'],
        ]);

        return redirect()->back()->with('success', 'Book added successfully!');
    }

    // delete book
    public function deleteBook(Book $book)
    {
        // check if teacher/admin is deleting
        if (!auth()->user()?->isTeacher() && !auth()->user()?->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // only delete custom created books
        if (!str_starts_with($book->ol_key, 'NO_OL_CUSTOM_')) {
            abort(403, 'You can only delete manually created books.');
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
}