<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavouriteBookController extends Controller
{
    // Add book to students favourites
    public function store(Request $request, $bookId)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return back()->with('error', 'Only students can favourite books.');
        }

        // check if book exists
        $book = DB::table('books')->where('id', $bookId)->first();
        if (!$book) {
            return back()->with('error', 'Book not found.');
        }

        // check if already favourited
        $exists = DB::table('student_favourite_books')
            ->where('student_id', $student->id)
            ->where('book_id', $bookId)
            ->exists();

        if ($exists) {
            return back()->with('info', 'This book is already in your favourites.');
        }

        DB::table('student_favourite_books')->insert([
            'school_id'    => $student->school_id,
            'classroom_id' => $student->classroom_id,
            'student_id'   => $student->id,
            'book_id'      => $bookId,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return back()->with('success', 'Book added to your favourites!');
    }

    // Remove book from students favourites
    public function destroy($bookId)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return back()->with('error', 'Only students can manage favourites.');
        }

        // delete favourite
        $deleted = DB::table('student_favourite_books')
            ->where('student_id', $student->id)
            ->where('book_id', $bookId)
            ->delete();

        if ($deleted) {
            return back()->with('success', 'Book removed from your favourites.');
        }

        return back()->with('error', 'Could not remove that book from your favourites.');
    }
}