<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProgressController extends Controller
{
    public function index()
    {
        // user,student and level
        $user = Auth::user();
        $student = $user->student;
        $level = $student->level ?? 0;
        
        // variables
        $completedBooks = collect();
        $currentlyReading = null;
        $readingHistory = collect();
        $totalBooks = 0;
        // genres, phonics and revied books
        $genresCount = [];
        $phonicsMastered = [];
        $reviewedBookIds = [];
        
        // stats
        $booksThisWeek = 0;
        $avgRating = 0.0;
        $chartData = [];
        $maxBooksInMonth = 0;

        if ($student) {
            // get completed and total books
            $completedBooks = $student->books()->wherePivot('status', 'completed')->get();
            $totalBooks = $completedBooks->count();
            
            // currently reading book
            $currentlyReading = $student->books()
                ->wherePivot('status', 'reading')
                ->latest('book_student.updated_at')
                ->first();
                
            // 10 most recently completed books
            $readingHistory = $student->books()
                ->wherePivot('status', 'completed')
                ->latest('book_student.updated_at')
                ->take(10)
                ->get();

            // books read this month
            $booksThisWeek = DB::table('book_student')
                ->where('student_id', $student->id)
                ->where('status', 'completed')
                ->where('updated_at', '>=', now()->startOfMonth())
                ->count();

            // average rating for completed books only
            $avgRating = DB::table('book_reviews')
                ->join('book_student', 'book_reviews.book_id', '=', 'book_student.book_id')
                ->where('book_reviews.student_id', $student->id)
                ->where('book_student.student_id', $student->id)
                ->whereIn('book_student.status', ['completed', 'reading'])
                ->avg('book_reviews.rating') ?? 0;
                
            $avgRating = number_format($avgRating, 1);

            // activity graph for past 6 months
            for ($i = 5; $i >= 0; $i--) {
                $monthStart = now()->subMonths($i)->startOfMonth();
                $monthEnd = now()->subMonths($i)->endOfMonth();
                $monthLabel = $monthStart->format('M');

                // count completed books in the month
                $count = DB::table('book_student')
                    ->where('student_id', $student->id)
                    ->where('status', 'completed')
                    ->whereBetween('updated_at', [$monthStart, $monthEnd])
                    ->count();

                // store in chart data
                $chartData[$monthLabel] = $count;
                if ($count > $maxBooksInMonth) {
                    $maxBooksInMonth = $count;
                }
            }

            // get genres count for completed books
            $genresCount = DB::table('book_student')
                ->where('book_student.student_id', $student->id)
                ->where('book_student.status', 'completed')
                ->join('book_genre', 'book_student.book_id', '=', 'book_genre.book_id')
                ->join('genres', 'book_genre.genre_id', '=', 'genres.id')
                ->select('genres.name', DB::raw('count(*) as total'))
                ->groupBy('genres.name')
                ->orderByDesc('total')
                ->pluck('total', 'name')
                ->toArray();

            // if no completed books, get genres from explored genres
            if (empty($genresCount)) {
                $exploredGenres = DB::table('genre_student')
                    ->where('student_id', $student->id)
                    ->join('genres', 'genre_student.genre_id', '=', 'genres.id')
                    ->pluck('genres.name');
                
                foreach ($exploredGenres as $genreName) {
                    $genresCount[$genreName] = 1;
                }
            }

            // get phonics learned from completed books
            $phonicsMastered = DB::table('book_student')
                ->where('book_student.student_id', $student->id)
                ->where('book_student.status', 'completed')
                ->join('book_phonic', 'book_student.book_id', '=', 'book_phonic.book_id')
                ->join('phonics', 'book_phonic.phonic_id', '=', 'phonics.id')
                ->select('phonics.sound')
                ->distinct()
                ->pluck('sound')
                ->toArray();
                
            // get reviewed books ids
            $reviewedBookIds = DB::table('book_reviews')
                ->where('student_id', $student->id)
                ->pluck('book_id')
                ->toArray();
        }

        // weekly target goal of students
        $goalRecord = DB::table('student_weekly_goals')
            ->where('student_id', $student->id)
            ->first();
        // default = 2
        $weeklyTarget = $goalRecord ? $goalRecord->target : 2;

        return view('progress', compact(
            'user', 
            'student', 
            'level', 
            'totalBooks', 
            'currentlyReading', 
            'readingHistory', 
            'genresCount', 
            'phonicsMastered',
            'booksThisWeek',
            'weeklyTarget',
            'avgRating',
            'chartData',
            'maxBooksInMonth',
            'reviewedBookIds'
        ));
    }
}