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

        // get average rating and streak count and top genre
        $avgRating = 0;
        $streakCount = 0;
        $topGenreText = 'None';

        // check if user has a student profile
        if ($user->student) {
            // average rating for completed books only
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
        }
            
        // average rating
        $avgRating = number_format($avgRating, 1);

        return view('dashboard', compact('user', 'avgRating', 'streakCount', 'topGenreText'));
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
            return view('user.show', compact('user', 'avgRating', 'streakCount', 'topGenreText', 'phonicsMastered', 'level'));
        }

        if ($currentUser->isAdmin()) {
            return view('user.show', compact('user', 'avgRating', 'streakCount', 'topGenreText', 'phonicsMastered', 'level'));
        }

        // make sure both users belong to a school
        if (!$currentUser->school_id || !$user->school_id) {
            abort(403, 'Unauthorized access.');
        }

        // allow if they belong to the same school
        if ($currentUser->school_id === $user->school_id) {
            return view('user.show', compact('user', 'avgRating', 'streakCount', 'topGenreText', 'phonicsMastered', 'level'));
        }

        // if not allowed, deny access
        abort(403, 'You are only allowed to view student profiles in your own classroom');
    }

    // Show own profile
    public function profile(){
        return redirect()->route('user.show', ['id' => Auth::id()]);
    }
}