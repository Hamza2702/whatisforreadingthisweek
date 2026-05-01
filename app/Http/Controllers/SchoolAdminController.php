<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\School;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Classroom;
use Illuminate\Support\Facades\Hash;
use App\Models\Genre;
use App\Models\Phonic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class SchoolAdminController extends Controller
{
    public function bannedBooks(Request $request)
    {
        // get school with banned books relationship to check if books are banned by the school
        $school = School::with('bannedBooks')->findOrFail(auth()->user()->school_id);
        
        $query = Book::query();
        $query->with('genres');

        // search
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }
        // author
        if ($request->filled('author')) {
            $query->where('author', 'like', '%' . $request->author . '%');
        }

        // minimum level
        if ($request->filled('level_min')) {
            $query->where('ort_level', '>=', $request->level_min);
        }

        // maximum level
        if ($request->filled('level_max')) {
            $query->where('ort_level', '<=', $request->level_max);
        }

        // genre
        if ($request->filled('genre')) {
            $genreSlugs = (array) $request->genre;
            $genreIds = Genre::whereIn('slug', $genreSlugs)->pluck('id');
            $bookIds = DB::table('book_genre')->whereIn('genre_id', $genreIds)->pluck('book_id');
            $query->whereIn('id', $bookIds);
        }
        
        // phonic
        if ($request->filled('phonic')) {
            $phonicIds = (array) $request->phonic; 
            $bookIds = DB::table('book_phonic')->whereIn('phonic_id', $phonicIds)->pluck('book_id');
            $query->whereIn('id', $bookIds);
        }

        // readable online
        if ($request->has('readable') && $request->readable == '1') {
            $query->where('ol_key', 'not like', 'NO_OL_%');
        }

        // status (banned)
        if ($request->filled('status')) {
            if ($request->status === 'banned') {
                $query->whereHas('bannedBySchools', function($q) use ($school) {
                    $q->where('school_id', $school->id);
                });
                // restricted
            } elseif ($request->status === 'restricted') {
                $query->whereHas('bannedBySchools', function($q) use ($school) {
                    $q->where('school_id', $school->id)->where('ban_type', 'restrict');
                });
                // hidden
            } elseif ($request->status === 'hidden') {
                $query->whereHas('bannedBySchools', function($q) use ($school) {
                    $q->where('school_id', $school->id)->where('ban_type', 'hide');
                });
                // unbanned
            } elseif ($request->status === 'unbanned') {
                $query->whereDoesntHave('bannedBySchools', function($q) use ($school) {
                    $q->where('school_id', $school->id);
                });
            }
        }

        // sort filters
        $sort = $request->get('sort', 'level-low');
        if ($sort === 'a-z') {
            $query->orderBy('title', 'asc');
        } elseif ($sort === 'level-low') {
            $query->orderBy('ort_level', 'asc');
        } elseif ($sort === 'level-high') {
            $query->orderBy('ort_level', 'desc');
        } elseif ($sort === 'author-a-z') {
            $query->orderBy('author', 'asc');
        } elseif ($sort === 'author-z-a') {
            $query->orderBy('author', 'desc');
        } elseif ($sort === 'custom') {
            $query->orderByRaw("CASE WHEN ol_key LIKE 'NO_OL_CUSTOM_%' THEN 0 ELSE 1 END")->orderBy('created_at', 'desc');
        } else {
            $query->latest();
        }

        // paginate
        $books = $query->paginate(28)->withQueryString(); 
        
        // genres/phonics
        $genres = Genre::orderBy('name')->get();
        $phonics = Phonic::orderBy('sound')->get(); 

        return view('schooladmin.banned-books', compact('school', 'books', 'genres', 'phonics'));
    }

    // Toggle ban of book
    public function toggleBan(Request $request, Book $book)
    {
        $school = School::findOrFail(auth()->user()->school_id);
        $action = $request->input('action'); // unban, restrict, hide

        // validation
        if ($action === 'unban') {
            $school->bannedBooks()->detach($book->id);
            $status = 'unbanned';
        } else {
            // add/update pivot table with the ban type
            $school->bannedBooks()->syncWithoutDetaching([
                $book->id => ['ban_type' => $action]
            ]);
            $school->bannedBooks()->updateExistingPivot($book->id, ['ban_type' => $action]);

            // message status
            $status = $action === 'hide' ? 'hidden entirely' : 'made unreadable';
        }
        return back()->with('success', "Book successfully {$status}.");
    }

    // Create teacher
    public function createTeacher()
    {
        // get all year groups from classrooms of current schooladmin
        $yearGroups = Classroom::query()
            ->where('teacher_id', auth()->id())
            ->withCount('students')
            ->orderBy('year_group')
            ->get()
            ->map(fn ($c) => [
                'year'     => "{$c->year_group}",
                'name'     => $c->name,
                'students' => $c->students_count,
                'slug'     => $c->id,
                'active'   => $c->active,
                'academic_year' => $c->academic_year,
                'is_progressed' => $c->is_progressed,
            ])->toArray();

        return view('schooladmin.teachers.create', compact('yearGroups'));
    }

    // Store teacher
    public function storeTeacher(Request $request)
    {
        // input validation
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email'    => 'required|email|max:255|unique:users,email',
            'phone'    => ['nullable', 'string', 'min:10', 'max:20', 'regex:/^[\d\s\+\-\(\)]+$/'], 
            'password' => 'required|string|min:8|confirmed',
            'pfp'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048', // max 2MB
        ], [
            'phone.regex' => 'The phone number format is invalid. It can only contain numbers, spaces, +, -, and ()',
            'phone.min'   => 'The phone number must be at least 10 characters long',
            'phone.max'   => 'The phone number cannot be longer than 20 characters',
        ]);

        $pfpPath = null;

        // upload custom pfp
        if ($request->hasFile('pfp')) {
            // store in public/pfp
            $path = $request->file('pfp')->store('pfp', 'public');
            // create pfp path
            $pfpPath = Storage::url($path);
        } else {
            // get a random image from pfps
            $pfpDirectory = public_path('images/pfp');  
            
            // if directory exists and has files
            if (File::isDirectory($pfpDirectory)) {
                $files = File::files($pfpDirectory);
                
                if (count($files) > 0) {
                    $randomFile = $files[array_rand($files)];
                    $pfpPath = '/images/pfp/' . $randomFile->getFilename();
                }
            }

            // if no directory or no files
            if (!$pfpPath) {
                $pfpPath = '/images/Placeholder.jpeg'; 
            }
        }

        // create teacher
        User::create([
            'name'      => $validated['name'],
            'username'  => $validated['username'],
            'email'     => $validated['email'],
            'email_verified_at' => now(),
            'phone'     => $validated['phone'],
            'password'  => Hash::make($validated['password']),
            'role'      => 'teacher', 
            'school_id' => auth()->user()->school_id, 
            'isAdmin'   => false,
            'pfp'       => $pfpPath,
        ]);

        return redirect()->route('teacher.index')->with('success', 'New teacher account created successfully!');
    }

    // DESTROY teacher
    public function destroyTeacher($id)
    {
        $teacher = User::findOrFail($id);

        if ($teacher->school_id !== auth()->user()->school_id || $teacher->id === auth()->id()) {
            abort(403, 'Unauthorised action');
        }

        // if a custom pfp exists, delete from storage too
        if ($teacher->pfp && str_starts_with($teacher->pfp, '/storage/')) {
            $path = str_replace('/storage/', '', $teacher->pfp);
            Storage::disk('public')->delete($path);
        }

        // delete teacher
        $teacher->delete();

        return redirect()->back()->with('success', 'Teacher removed successfully.');
    }

}