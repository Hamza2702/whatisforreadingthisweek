<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiteController extends Controller
{
    public function index()
    {
        // counts
        $schoolsCount = DB::table('schools')->count();
        $pupilsCount = DB::table('students')->count();
        $booksCount = DB::table('books')->count();
        $phonicsCount = DB::table('phonics')->count();
        $booksAssignedCount = DB::table('book_student')->count();
        $favouritedCount = DB::table('student_favourite_books')->count();
        $activeStreaksCount = DB::table('student_streaks')->where('streak_count', '>', 0)->count();
        $reviewsCount = DB::table('book_reviews')->count();
        $teachersCount = DB::table('users')->where('role','=','teacher')->count();

        // authors
        $authorsPath = database_path('data/Authors.csv');
        $authorsCount = 0;
        // get csv content
        if (file_exists($authorsPath)) {
            $file = fopen($authorsPath, 'r');

            if ($file !== false) {
                // skip header row
                fgetcsv($file);

                while (fgetcsv($file) !== false) {
                    $authorsCount++;
                }

                fclose($file);
            }
        }

        return view('Site.index', compact('schoolsCount', 'pupilsCount', 'booksCount', 'phonicsCount', 'booksAssignedCount', 'favouritedCount', 'activeStreaksCount', 'reviewsCount', 'authorsCount', 'teachersCount'));
    }
}