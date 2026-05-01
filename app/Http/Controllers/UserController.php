<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\File;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UserController extends Controller
{ 
    // Create
    public function create(){
        // Get schools for registering
        $schools = School::orderBy('name')->get();

        return view('auth.register', [
            'schools' => $schools
        ]);
    }

    // Store new user
    public function store(){
        $attributes = request()->validate(['name'=>['required'],
            'username'=>['required','string','unique:users,username','max:255'],
            'email'=>['required','email','unique:users,email','confirmed'],
            'phone' => ['required', 'string', 'min:10', 'max:15'],
            'password'=>['confirmed','required',Password::min(8)->mixedCase()->numbers()->symbols()],
            'school_id' => ['required', 'exists:schools,id']
        ]);
        
        $images = [];
        $files = File::files('images/pfp');
        foreach($files as $file){
            // Check if its an image file
            $extension = strtolower($file->getExtension());
            if(in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])){
                // Convert path to web URL
                $relativePath = 'images/pfp/' . $file->getFilename();
                $images[] = '/' . $relativePath;
            }
        }

        $attributes['pfp'] = $images[array_rand($images)];
        $user = User::create($attributes);
        Auth::login($user);
        
        return redirect('/');
    }

    public function dashboard()
    {
        $user = Auth::user();
        // load school and student relationships for user
        $user->load('school', 'student');

        // get variables
        $booksReadCount = 0;
        $genresExploredCount = 0;
        $phonicsMasteredCount = 0;
        $avgRating = 0;
        $streakCount = 0;
        $topGenreText = 'None';
        $phonicsMastered =[];
        $level = $user->student ? $user->student->level : 0;
        $favouriteBooks = collect();

        // check if user has a student profile
        if ($user->student) {
            // total books read
            $booksReadCount = DB::table('book_student')
                ->where('student_id', $user->student->id)
                ->where('status', 'completed')
                ->count();

            // phonics mastered
            $phonicsMastered = DB::table('book_student')
                ->where('book_student.student_id', $user->student->id)
                ->where('book_student.status', 'completed')
                ->join('book_phonic', 'book_student.book_id', '=', 'book_phonic.book_id')
                ->join('phonics', 'book_phonic.phonic_id', '=', 'phonics.id')
                ->select('phonics.sound')
                ->distinct()
                ->pluck('sound')
                ->toArray();
                
            $phonicsMasteredCount = count($phonicsMastered);

            // average rating for completed books
            $avgRating = DB::table('book_reviews')
                ->join('book_student', 'book_reviews.book_id', '=', 'book_student.book_id')
                ->where('book_reviews.student_id', $user->student->id)
                ->where('book_student.student_id', $user->student->id)
                ->whereIn('book_student.status', ['completed', 'reading'])
                ->avg('book_reviews.rating') ?? 0;

            // get streak 
            $streakRecord = DB::table('student_streaks')
                ->where('student_id', $user->student->id)
                ->first();

            // if they have a streak record
            if ($streakRecord) {
                // get last read date if it exists
                $lastReadDate = $streakRecord->last_read_at ? Carbon::parse($streakRecord->last_read_at) : null;
                
                // if past 7 days, reset streak to 0 and update
                if ($lastReadDate && Carbon::now()->diffInDays($lastReadDate) > 7) {
                    $streakCount = 0;
                    DB::table('student_streaks')
                        ->where('student_id', $user->student->id)
                        ->update(['streak_count' => 0, 'updated_at' => Carbon::now()]);
                } else {
                    // get current streak count
                    $streakCount = $streakRecord->streak_count;
                }
            }

            // get top genre
            $genres = DB::table('genre_student')
                ->join('genres', 'genre_student.genre_id', '=', 'genres.id')
                ->where('genre_student.student_id', $user->student->id)
                ->select('genres.name', DB::raw('COUNT(*) as total'))
                ->groupBy('genres.name')
                ->orderByDesc('total')
                ->get();

            $genresExploredCount = $genres->count();
            // find highest count of genres or any tied genres 
            if ($genres->isNotEmpty()) {
                $highestCount = $genres->first()->total;
                $topGenres = $genres->where('total', $highestCount)->pluck('name');

                // if 2 tied genres show both
                if ($topGenres->count() == 2) {
                    $topGenreText = $topGenres[0] . ' & ' . $topGenres[1];
                } else {
                    $topGenreText = $topGenres->first();
                }
            }
            // get 20 favourite books
            $favouriteBooks = DB::table('student_favourite_books')
                ->join('books', 'student_favourite_books.book_id', '=', 'books.id')
                ->where('student_favourite_books.student_id', $user->student->id)
                ->select('books.id', 'books.title', 'books.author', 'books.cover_id')
                ->latest('student_favourite_books.created_at')
                ->take(20)
                ->get();
        }
            
        // average rating
        $avgRating = number_format($avgRating, 1);

        return view('dashboard', compact('user', 'avgRating', 'streakCount', 'topGenreText', 'phonicsMastered', 'level', 'favouriteBooks', 'booksReadCount', 'genresExploredCount', 'phonicsMasteredCount'));
    }

    public function show($id){
        $user = User::with('school', 'student')->findOrFail($id);
        $currentUser = Auth::user();

        // get average rating and streak count and top genre and phonics
        $avgRating = 0;
        $streakCount = 0;
        $topGenreText = 'None';
        $phonicsMastered = [];
        $level = $user->student ? $user->student->level : 0;
        $favouriteBooks = collect();

        // check if user has a student profile
        if ($user->student) {
            // average rating for completed books only
            $avgRating = DB::table('book_reviews')
                ->join('book_student', 'book_reviews.book_id', '=', 'book_student.book_id')
                ->where('book_reviews.student_id', $user->student->id)
                ->where('book_student.student_id', $user->student->id)
                ->whereIn('book_student.status', ['completed', 'reading'])
                ->avg('book_reviews.rating') ?? 0;

            // get streak record for student
            $streakRecord = DB::table('student_streaks')
                ->where('student_id', $user->student->id)
                ->first();

            // if they have a streak record
            if ($streakRecord) {
                // get last read date if it exists
                $lastReadDate = $streakRecord->last_read_at ? Carbon::parse($streakRecord->last_read_at) : null;
                
                // if past 7 days, reset streak to 0 and update
                if ($lastReadDate && Carbon::now()->diffInDays($lastReadDate) > 7) {
                    $streakCount = 0;
                    DB::table('student_streaks')
                        ->where('student_id', $user->student->id)
                        ->update(['streak_count' => 0, 'updated_at' => Carbon::now()]);
                } else {
                    // get current streak count
                    $streakCount = $streakRecord->streak_count;
                }
            }

            // get 20 favourite books
            $favouriteBooks = DB::table('student_favourite_books')
                ->join('books', 'student_favourite_books.book_id', '=', 'books.id')
                ->where('student_favourite_books.student_id', $user->student->id)
                ->select('books.id', 'books.title', 'books.author', 'books.cover_id')
                ->latest('student_favourite_books.created_at')
                ->take(20) 
                ->get();

            // get top genre
            $genres = DB::table('genre_student')
                ->join('genres', 'genre_student.genre_id', '=', 'genres.id')
                ->where('genre_student.student_id', $user->student->id)
                ->select('genres.name', DB::raw('COUNT(*) as total'))
                ->groupBy('genres.name')
                ->orderByDesc('total')
                ->get();

            // find highest count of genres or any tied genres 
            if ($genres->isNotEmpty()) {
                $highestCount = $genres->first()->total;
                $topGenres = $genres->where('total', $highestCount)->pluck('name');

                // if 2 tied genres show both
                if ($topGenres->count() == 2) {
                    $topGenreText = $topGenres[0] . ' & ' . $topGenres[1];
                } else {
                    $topGenreText = $topGenres->first();
                }
            }

            // get phonics learned from completed books
            $phonicsMastered = DB::table('book_student')
                ->where('book_student.student_id', $user->student->id)
                ->where('book_student.status', 'completed')
                ->join('book_phonic', 'book_student.book_id', '=', 'book_phonic.book_id')
                ->join('phonics', 'book_phonic.phonic_id', '=', 'phonics.id')
                ->select('phonics.sound')
                ->distinct()
                ->pluck('sound')
                ->toArray();
        }
            
        // average rating
        $avgRating = number_format($avgRating, 1);

        // allow if user is visiting own profile
        if ($currentUser->id === $user->id){
            return view('user.show', compact('user', 'avgRating', 'streakCount', 'topGenreText', 'phonicsMastered', 'level', 'favouriteBooks'));
        }

        if ($currentUser->isAdmin()) {
            return view('user.show', compact('user', 'avgRating', 'streakCount', 'topGenreText', 'phonicsMastered', 'level', 'favouriteBooks' ));
        }

        // make sure both users belong to a school
        if (!$currentUser->school_id || !$user->school_id) {
            abort(403, 'Unauthorized access.');
        }

        // allow if they belong to the same school
        if ($currentUser->school_id === $user->school_id) {
            return view('user.show', compact('user', 'avgRating', 'streakCount', 'topGenreText', 'phonicsMastered', 'level', 'favouriteBooks'));
        }

        // if not allowed, deny access
        abort(403, 'You are only allowed to view student profiles in your own classroom');
    }

    // Show own profile
    public function profile(){
        return redirect()->route('user.show', ['id' => Auth::id()]);
    }

    // Show forgot password form
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    // Submit forgot password (create announcement)
    public function submitForgotPassword(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string', 'max:255'],
        ]);

        // find user by username
        $user = User::where('username', $request->username)->first();

        // not a user
        if (!$user) {
            return back()->withErrors([
                'username' => 'No account found with that username. Please check the spelling and try again.'
            ])->withInput();
        }

        // student table
        $student = DB::table('students')
            ->where('user_id', $user->id)
            ->where('active', 1)
            ->first();

        // not a student acc
        if (!$student) {
            return back()->withErrors([
                'username' => 'This account is not a student account. Only students can request password resets.'
            ])->withInput();
        }

        // not assigned to classroom
        if (!$student->classroom_id) {
            return back()->withErrors([
                'username' => 'You are not currently assigned to a classroom. Please contact your school administrator.'
            ])->withInput();
        }

        // find classroom
        $classroom = DB::table('classrooms')
            ->where('id', $student->classroom_id)
            ->first();

        // classroom doesnt exist
        if (!$classroom) {
            return back()->withErrors([
                'username' => 'Your classroom could not be found. Please contact your school administrator.'
            ])->withInput();
        }

        // stop spammng (1 per day)
        $recentRequest = DB::table('announcements')
            ->where('classroom_id', $student->classroom_id)
            ->where('student_id', $student->id)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($recentRequest) {
            return back()->withErrors([
                'username' => 'A password reset has already been requested in the last 24 hours. Please ask your teacher for help, or try again tomorrow.'
            ])->withInput();
        }

        // announce the teacher
        DB::table('announcements')->insert([
            'school_id'    => $student->school_id,
            'classroom_id' => $student->classroom_id,
            'student_id'   => $student->id,
            'message'      => "@{$user->username} ({$student->first_name} {$student->last_name}) has requested a password reset. Click the reset button in their profile management to generate a new password for them.",
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', 'Your password reset request has been sent to your teacher. Please ask them in person to reset it for you.');
    }
}