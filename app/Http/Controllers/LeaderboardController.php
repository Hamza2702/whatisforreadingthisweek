<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Classroom;
use App\Models\LeaderboardHistory;
use App\Models\Student;

class LeaderboardController extends Controller
{
    public function show(Request $request, $classroomId = null)
    {
        $user = auth()->user();
        
        // check whose accessing leaderboard
        if (in_array(strtolower($user->role), ['teacher', 'schooladmin'])) {
            if (!$classroomId) {
                abort(403, 'You must specify a classroom to view its leaderboard.');
            }
            $targetClassroomId = $classroomId;
        } else {
            $student = $user->student;
            if (!$student || !$student->classroom_id) {
                abort(403, 'You must be assigned to a classroom to view the leaderboard.');
            }
            $targetClassroomId = $student->classroom_id;
        }

        // get class
        $class = Classroom::find($targetClassroomId);

        // testing next month
        if ($request->has('test_month') && $request->test_month === 'next') {
            $simulatedNow = now()->addMonth();
        } else {
            $simulatedNow = now();
        }

        $currentMonth = $simulatedNow->month;
        $currentYear = $simulatedNow->year;
        $monthName = $simulatedNow->format('F');

        // testing end of month leaderboard reset
        if ($request->has('test') && $request->test == 'true') {
            $targetTime = now()->addSeconds(5); 
        } else {
            $targetTime = $simulatedNow->copy()->endOfMonth();
        }
        
        $targetDateIso = $targetTime->toIso8601String();

        // save previous month data
        $prevDate = $simulatedNow->copy()->subMonth();
        
        // check if previous month data is already archived
        $archived = LeaderboardHistory::where('classroom_id', $targetClassroomId)
            ->where('month', $prevDate->month)
            ->where('year', $prevDate->year)
            ->exists();

        // if not archived, archive previous month data
        if (!$archived) {
            $prevStudents = Student::where('classroom_id', $targetClassroomId)
                ->where('active', true)
                ->withCount(['books as books_read_count' => function ($query) use ($prevDate) {
                    $query->where('book_student.status', 'completed')
                        ->whereMonth('book_student.updated_at', $prevDate->month)
                        ->whereYear('book_student.updated_at', $prevDate->year);
                }])->get();

            // bulk insert history data
            $historyData = [];
            foreach ($prevStudents as $pStudent) {
                $historyData[] = [
                    'school_id' => $pStudent->school_id,
                    'classroom_id' => $targetClassroomId,
                    'student_id' => $pStudent->id,
                    'books_read' => $pStudent->books_read_count ?? 0,
                    'month' => $prevDate->month,
                    'year' => $prevDate->year,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            if (!empty($historyData)) {
                LeaderboardHistory::insert($historyData);
            }
        }
 
        // ===========================================================
        // load students with completed books count for curr month
        $monthlyStudents = Student::where('classroom_id', $targetClassroomId)
            ->where('active', true)
            ->with('user') 
            ->withCount(['books as books_read_count' => function ($query) use ($currentMonth, $currentYear) {
                $query->where('book_student.status', 'completed')
                      ->whereMonth('book_student.updated_at', $currentMonth)
                      ->whereYear('book_student.updated_at', $currentYear);
            }])
            ->orderByDesc('books_read_count') 
            ->orderBy('first_name') 
            ->get();

        // load students with completed books count for all time
        $allTimeStudents = Student::where('classroom_id', $targetClassroomId)
            ->where('active', true)
            ->with('user') 
            ->withCount(['books as all_time_read_count' => function ($query) {
                $query->where('book_student.status', 'completed');
            }])
            ->orderByDesc('all_time_read_count') 
            ->orderBy('first_name') 
            ->get();

        return view('leaderboard', compact('monthlyStudents', 'allTimeStudents', 'monthName', 'targetDateIso', 'class'));
    }
}