<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect('/')->with('error', 'Student not found');
        }

        // Get current assignments with status reading (newest first) and get due dates
        $currentAssignments = $student->books()
            ->wherePivot('status', 'reading')
            ->orderByDesc('book_student.created_at')
            ->get()
            ->map(function ($book) {
                // calculate due dates
                $assignedDate = Carbon::parse($book->pivot->created_at);
                $book->due_date = $assignedDate->copy()->addDays(7);
                $book->is_overdue = Carbon::now()->gt($book->due_date);
                return $book;
            });

        // Get the entire reading history and show newest first with pagination
        $readingHistory = $student->books()
            ->wherePivot('status', 'completed')
            ->orderByDesc('book_student.updated_at')
            ->paginate(10);

        // Get all reviews for student
        $reviews = DB::table('book_reviews')
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('book_id'); // $reviews[$book->id] gets review for specific book

        return view('assignments', compact('student', 'currentAssignments', 'readingHistory', 'reviews'));
    }

    // Mark book as completed and redirect student to review page
    public function markCompleted(Request $request, $bookId)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->back();
        }
        // Update pivot table to complete book
        DB::table('book_student')
            ->where('student_id', $student->id)
            ->where('book_id', $bookId)
            ->update([
                'status' => 'completed',
                'updated_at' => Carbon::now()
            ]);

        // check if student has met their weekly goal
        $weeklyGoal = DB::table('student_weekly_goals')
            ->where('student_id', $student->id)
            ->first();

        if ($weeklyGoal) {
            // count how many books the student has completed this week
            $booksCompletedThisWeek = DB::table('book_student')
                ->where('student_id', $student->id)
                ->where('status', 'completed')
                ->whereBetween('updated_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek(),
                ])
                ->count();

            // only update streak if theyve hit their target
            if ($booksCompletedThisWeek >= $weeklyGoal->target) {
                $streakRecord = DB::table('student_streaks')
                    ->where('student_id', $student->id)
                    ->first();

                if ($streakRecord) {
                    $lastReadDate = $streakRecord->last_read_at ? Carbon::parse($streakRecord->last_read_at) : null;

                    // only increment once per week
                    if (!$lastReadDate || !$lastReadDate->isSameWeek(Carbon::now())) {
                        DB::table('student_streaks')
                            ->where('student_id', $student->id)
                            ->update([
                                'last_read_at' => Carbon::now(),
                                'streak_count' => DB::raw('streak_count + 1'),
                                'updated_at' => Carbon::now(),
                            ]);
                    }
                }
            }
        }

        return redirect('/books/' . $bookId . '/review')->with('success', 'Good job reading! Write a review for your logbook!');
    }

    // Notify teacher that student has completed the book assignment
    public function notifyTeacher(Request $request)
    {
        $user = Auth::user();
        $student = $user->student;

        if (!$student) {
            return redirect()->back()->with('error', 'Student profile not found.');
        }

        // Get the classroom and find teacher who owns it
        $classroom = DB::table('classrooms')
            ->where('id', $student->classroom_id)
            ->select('teacher_id')
            ->first();

        // couldn't find
        if (!$classroom || !$classroom->teacher_id) {
            return redirect()->back()->with('error', 'Could not find a teacher to notify');
        }

        // check db for a notification in the past day to stop spam - BAD CODE VERY BAD BUT I CBA TO MAKE A TYPE SO IM GOING WEITH IT
        $recentNotification = DB::table('announcements')
            ->where('student_id', $student->id)
            ->where('classroom_id', $student->classroom_id)
            ->where('message', 'like', '%has completed their books and is waiting to be assigned another%')
            ->where('created_at', '>=', Carbon::now()->subHours(48))
            ->first();

        // if already notified
        if ($recentNotification) {
            return redirect()->back()->with('error', 'You have already notified your teacher, please wait until they assign you another book.');
        }

        // Add message to announcements db for teacher
        DB::table('announcements')->insert([
            'school_id' => $student->school_id,
            'classroom_id' => $student->classroom_id,
            'student_id' => $student->id,
            'message' => $user->name . ' has completed their books and is waiting to be assigned another!',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Your teacher has been notified!');
    }
}