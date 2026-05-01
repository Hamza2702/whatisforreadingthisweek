<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use Carbon\Carbon;

class ClassroomController extends Controller
{
    // Delete class
    public function removeClassroom(Classroom $classroom)
    {
        // check if teacher owns the class
        if($classroom->teacher_id !== auth()->id()){
            abort(403, "Unauthorised action");
        }
        // detach students from classroom
        Student::where('classroom_id', $classroom->id)->update(['classroom_id' => null]);
        $classroom->students()->detach();
        // delete archive classroom and class
        DB::table('archive_classrooms')->where('classroom_id', $classroom->id)->delete();
        $classroom->delete();

        return redirect()->route('teacher.index')->with('success', 'Classroom and archive have been deleted');
    }

    // Archive classroom
    public function archiveClassroom(Request $request, $id)
    {
        // get classroom
        $classroom = Classroom::findOrFail($id);

        // check if classroom is already archived
        if (!$classroom->active) {
            return redirect()->back()->with('error', 'This classroom is already archived.');
        }

        // get student ids part of the ACTIVE classroom
        $studentIds = $classroom->students()
            ->wherePivot('active', 1)
            ->pluck('students.id')
            ->toArray();

        // database transaction
        DB::transaction(function () use ($classroom, $studentIds) {
            
            // insert data into archive classrooms table
            DB::table('archive_classrooms')->insert([
                'school_id'     => $classroom->school_id,
                'classroom_id'  => $classroom->id,
                'student_ids'   => json_encode($studentIds),
                'academic_year' => $classroom->academic_year,
                'year_group'    => $classroom->year_group,
                'stage'         => $classroom->stage,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            // make the classroom inactive
            $classroom->update(['active' => 0]);

            // update pivot table and set end date for ACTIVE students
            DB::table('classroom_student')
                ->where('classroom_id', $classroom->id)
                ->where('active', 1)
                ->update([
                    'active'  => 0,
                    'ends_on' => now(),
                ]);

            // unassign classroom id for students -> will help for placing students in a new classrom new year
            if (!empty($studentIds)) {
                Student::whereIn('id', $studentIds)->update([
                    'classroom_id' => null
                ]);
            }
        });

        return redirect()->route('teacher.index')->with(
            'success', 
            'The classroom is now archived. Progress the students to the next year.'
        );
    }

    // Restore classroom
    public function restoreClassroom(Request $request, $id)
    {
        // find classroom
        $classroom = Classroom::findOrFail($id);

        // prevent duplicate (check if its already active)
        if ($classroom->active) {
            return redirect()->back()->with('error', 'This classroom is already active.');
        }

        // check if classroom has progressed
        if ($classroom->is_progressed) {
            return redirect()->back()->with(
                'error',
                'This classroom cannot be restored because it has already been progressed. The students are in the next year\'s classroom.'
            );
        }

        $skippedStudents = [];

        // DB transaction
        DB::transaction(function () use ($classroom, &$skippedStudents) {
            // reactive classroom
            $classroom->update(['active' => 1]);

            // find archive record and find classroom students
            $archive = DB::table('archive_classrooms')
                ->where('classroom_id', $classroom->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($archive && $archive->student_ids) {
                $studentIds = json_decode($archive->student_ids, true);

                if (!empty($studentIds)) {
                    // reassign classroom_id to students if they're not active elsewhere
                    $activeElsewhere = DB::table('classroom_student')
                        ->whereIn('student_id', $studentIds)
                        ->where('classroom_id', '!=', $classroom->id)
                        ->where('active', 1)
                        ->pluck('student_id')
                        ->toArray();

                    if (!empty($activeElsewhere)) {
                        $skippedStudents = Student::whereIn('id', $activeElsewhere)
                            ->get()
                            ->map(fn($s) => "{$s->first_name} {$s->last_name}")
                            ->toArray();
                    }

                    $studentIdsToRestore = array_diff($studentIds, $activeElsewhere);

                    // restoring students
                    if (!empty($studentIdsToRestore)) {
                        Student::whereIn('id', $studentIdsToRestore)->update([
                            'classroom_id' => $classroom->id
                        ]);

                        // update students in class
                        DB::table('classroom_student')
                            ->where('classroom_id', $classroom->id)
                            ->whereIn('student_id', $studentIdsToRestore)
                            ->update([
                                'active'  => 1,
                                'ends_on' => null,
                            ]);
                    }
                }

                // delete archive record as the class is back
                DB::table('archive_classrooms')->where('id', $archive->id)->delete();
            }
        });

        // success messages
        $message = 'Classroom restored! Students have been placed back into the class.';

        if (!empty($skippedStudents)) {
            $count = count($skippedStudents);
            $names = implode(', ', $skippedStudents);
            $message .= $count === 1
                // skipped student
                ? " Note: 1 student ({$names}) was skipped because they are currently active in another classroom."
                // skipped students
                : " Note: {$count} students ({$names}) were skipped because they are currently active in another classroom.";
        }

        return redirect()->route('teacher.index')->with('success', $message);
    }

    // Progress classroom
    public function progressClassroom(Request $request, $id)
    {
        // find old archived class
        $oldClassroom = Classroom::findOrFail($id);

        // dont progress an active classroom
        if ($oldClassroom->active) {
            return redirect()->back()->with('error', 'You must archive this classroom before progressing it.');
        }

        // year 6 cant be progressed
        if ($oldClassroom->year_group >= 6) {
            return redirect()->back()->with('error', 'Year 6 classes cannot be progressed further.');
        }

        // db transaction
        DB::transaction(function () use ($oldClassroom) {
            
            // get new values for the year
            $currentStart = (int) $oldClassroom->academic_start;
            $currentEnd   = (int) $oldClassroom->academic_end;

            // if its missing
            if ($currentStart < 2000 || $currentEnd < 2000) {
                // try get the date
                preg_match_all('/\d{4}/', $oldClassroom->academic_year, $matches);
                if (!empty($matches[0]) && count($matches[0]) >= 2) {
                    $currentStart = (int) $matches[0][0];
                    $currentEnd   = (int) $matches[0][1];
                } else {
                    // if it fails, make a guess on the current date
                    $currentStart = date('m') >= 9 ? (int) date('Y') : (int) date('Y') - 1;
                    $currentEnd   = $currentStart + 1;
                }

                // fix old classrooms data
                $oldClassroom->update([
                    'academic_start' => $currentStart,
                    'academic_end'   => $currentEnd,
                    'academic_year'  => $currentStart . '-' . $currentEnd
                ]);
            }

            $nextYearGroup    = $oldClassroom->year_group + 1;
            $nextStart        = $currentStart + 1;
            $nextEnd          = $currentEnd + 1;
            $nextAcademicYear = $nextStart . '-' . $nextEnd;
            $nextStage        = $nextYearGroup <= 2 ? 'KS1' : 'KS2';

            // create new classroom
            $newClassroom = Classroom::create([
                'school_id'      => $oldClassroom->school_id,
                'teacher_id'     => $oldClassroom->teacher_id,
                'name'           => $oldClassroom->name, // Keeping the same name
                'year_group'     => $nextYearGroup,
                'stage'          => $nextStage,
                'academic_year'  => $nextAcademicYear,
                'academic_start' => $nextStart,
                'academic_end'   => $nextEnd,
                'active'         => true,
            ]);

            // update old classroom as progressed
            $oldClassroom->update(['is_progressed' => true]);

            // find archive record to figure out who was in old class
            $archive = DB::table('archive_classrooms')
                ->where('classroom_id', $oldClassroom->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($archive && $archive->student_ids) {
                $studentIds = json_decode($archive->student_ids, true);

                if (!empty($studentIds)) {
                    // update students table to place them in new classroom
                    Student::whereIn('id', $studentIds)->update([
                        'classroom_id' => $newClassroom->id
                    ]);

                    // insert new records into classroom_student pivot table
                    $pivotData = [];
                    foreach ($studentIds as $studentId) {
                        $pivotData[] = [
                            'school_id'    => $newClassroom->school_id,
                            'classroom_id' => $newClassroom->id,
                            'student_id'   => $studentId,
                            'starts_on'   => now(),
                            'ends_on'     => now()->addYear(),
                            'active'       => 1,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                    DB::table('classroom_student')->insert($pivotData);
                }
            }
        });

        return redirect()->route('teacher.index')->with(
            'success', 
            'Classroom progressed! A new classroom has been created for Year ' . ($oldClassroom->year_group + 1) . '.'
        );
    }

     // Create announcement view
    public function createAnnouncement(Classroom $classroom)
    {
        // verify ownership
        if ($classroom->teacher_id !== auth()->id()) {
            abort(403, "Unauthorised action");
        }

        // get all active students for dropdown menu
        $students = $classroom->students()->wherePivot('active', 1)->orderBy('first_name')->get();

        return view('teacher.classes.create-announcement', compact('classroom', 'students'));
    }

    // Store announcement
    public function storeAnnouncement(Request $request, Classroom $classroom)
    {
        // verify ownership
        if ($classroom->teacher_id !== auth()->id()) {
            abort(403, "Unauthorised action");
        }

        // validation
        $request->validate([
            'message' => 'required|string|max:1000',
            'entire_class' => 'nullable|boolean',
            'student_id' => 'nullable|exists:students,id'
        ]);

        // if entire class is unchecked, validate a student has been selected
        if (!$request->entire_class && empty($request->student_id)) {
            return back()->withErrors(['student_id' => 'Please select a student or check the entire class'])->withInput();
        }

        // insert into db
        $studentId = $request->entire_class ? null : $request->student_id;

        // stop spam!
        $duplicate = DB::table('announcements')
            ->where('classroom_id', $classroom->id)
            ->where('student_id', $studentId)
            ->where('message', $request->message)
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();

        if ($duplicate) {
            return redirect()
                ->route('teacher.classes.view', $classroom->id)
                ->with('success', 'Announcement posted successfully!');// successful announcement
        }

        DB::table('announcements')->insert([
            'school_id'    => $classroom->school_id,
            'classroom_id' => $classroom->id,
            'student_id'   => $studentId,
            'message'      => $request->message,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        return redirect()->route('teacher.classes.view', $classroom->id)->with('success', 'Announcement posted successfully!');
    }

    // Hide an announcement for individual students
    public function hideAnnouncement(Request $request, $id)
    {
        // get student
        $student = auth()->user()->student;

        if ($student) {
            DB::table('hidden_announcements')->insertOrIgnore([
                'school_id'       => $student->school_id,
                'student_id'      => $student->id,
                'announcement_id' => $id,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);
        }

        return back();
    }

    // Restore announcements from the last 30 days
    public function restoreAnnouncements(Request $request)
    {
        $student = auth()->user()->student;

        if ($student) {
            // find the ids of announcements created in the last month
            $recentAnnouncementIds = DB::table('announcements')
                ->where('created_at', '>=', now()->subMonth())
                ->pluck('id');

            // unhide them for the student
            DB::table('hidden_announcements')
                ->where('student_id', $student->id)
                ->whereIn('announcement_id', $recentAnnouncementIds)
                ->delete();
        }

        return back();
    }

    // View archived classroom statistics
    public function showStatistics(Request $request, $id)
    {
        // get classroom
        $classroom = Classroom::findOrFail($id);
        $this->ensureOwnsClassroom($classroom);

        // get yeargroups/stats method
        $yearGroups = $this->yearGroupsForTeacher(auth()->id());
        $stats = $this->buildClassroomStatistics($classroom);

        // if empty
        if (isset($stats['noData'])) {
            return view('teacher.classes.statistics', [
                'classroom'  => $classroom,
                'archive'    => $stats['archive'],
                'yearGroups' => $yearGroups,
                'noData'     => true,
            ]);
        }

        return view('teacher.classes.statistics', array_merge(
            $stats,
            compact('classroom', 'yearGroups')
        ));
    }

    // Build classroom stat data
    private function buildClassroomStatistics(Classroom $classroom): array
    {
        // archived classrooms
        $archive = DB::table('archive_classrooms')
            ->where('classroom_id', $classroom->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // get students
        $studentIds = DB::table('classroom_student')
            ->where('classroom_id', $classroom->id)
            ->distinct()
            ->pluck('student_id')
            ->toArray();

        // if no students
        if (empty($studentIds)) {
            return ['noData' => true, 'archive' => $archive];
        }

        $academicStart = (int)($classroom->academic_start ?? (now()->year - 1));
        $academicEnd   = (int)($classroom->academic_end ?? now()->year);
        $yearStart     = Carbon::create($academicStart, 9, 1)->startOfDay();
        $yearEnd       = Carbon::create($academicEnd, 8, 31)->endOfDay();

        // for active classrooms, limit chart to now for no empty future months
        // archived = cap at archived date
        if (!$classroom->active && $archive && $archive->created_at) {
            $archiveDate = Carbon::parse($archive->created_at)->endOfWeek();
            if ($archiveDate->lt($yearEnd)) {
                $yearEnd = $archiveDate;
            }
        } elseif ($classroom->active) {
            $now = now()->endOfWeek();
            if ($now->lt($yearEnd)) {
                $yearEnd = $now;
            }
        }

        $students = Student::whereIn('id', $studentIds)->with(['user'])->get();

        // =====================================
        // COMPLETED BOOKS
        $completedBooks = DB::table('book_student')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$yearStart, $yearEnd])
            ->get();

        $totalBooks = $completedBooks->count();

        // =====================================
        // RATING/DIFFICULTY
        $avgRating = 0;
        $avgDifficultyScore = 0;
        $totalReviews = 0;
        $avgDifficulty = 'N/A';

        // book reviews
        $reviews = DB::table('book_reviews')
            ->whereIn('student_id', $studentIds)
            ->whereBetween('updated_at', [$yearStart, $yearEnd])
            ->get();

        // get average rating, reviews and difficulty
        if ($reviews->isNotEmpty()) {
            $avgRating = round($reviews->whereNotNull('rating')->avg('rating') ?? 0, 1);
            $totalReviews = $reviews->whereNotNull('rating')->count();

            $avgDifficulty = $reviews->whereNotNull('difficulty')
                ->groupBy('difficulty')
                ->map->count()
                ->sortDesc()
                ->keys()
                ->first() ?? 'N/A';
        }

        // =====================================
        // MONTHLY ACTIVITY READING CHART DATA
        $chartData = [];
        $cursor = $yearStart->copy();
        while ($cursor->lte($yearEnd)) {
            $monthLabel = $cursor->format('M Y');
            $chartData[$monthLabel] = $completedBooks->filter(function ($b) use ($cursor) {
                $d = Carbon::parse($b->updated_at);
                return $d->year === $cursor->year && $d->month === $cursor->month;
            })->count();
            $cursor->addMonth();
        }

        // =====================================
        // PER STUDENT BOOK COUNTS
        $studentBookCounts = $completedBooks->groupBy('student_id')->map->count();

        $studentsWithCounts = $students->map(function ($s) use ($studentBookCounts) {
            $s->books_read = $studentBookCounts[$s->id] ?? 0;
            return $s;
        });

        // top readers / needs encouragement
        $topReaders = $studentsWithCounts->sortByDesc('books_read')->take(5)->values();
        $needsEncouragement = $studentsWithCounts->sortBy('books_read')->take(5)->values();

        // =====================================
        // READING LEVEL DISTRIBUTION
        $levelDistribution = $students->groupBy('level')
            ->map->count()
            ->sortKeys()
            ->toArray();

        // average level
        $avgLevel = round($students->avg('level') ?? 0, 1);

        // =====================================
        // WEEKLY GOAK TRACKING
        $studentGoals = DB::table('student_weekly_goals')
            ->whereIn('student_id', $studentIds)
            ->pluck('target', 'student_id')
            ->toArray();

        $weeks = [];
        $weekCursor = $yearStart->copy()->startOfWeek();
        $weekIndex = 1;

        while ($weekCursor->lte($yearEnd) && $weekCursor->lte(now())) {
            $weekStart = $weekCursor->copy();
            $weekEnd   = $weekCursor->copy()->endOfWeek();

            // student HIT OR MISS goals
            $studentsHitGoal = 0;
            $studentsMissedGoal = 0;

            foreach ($students as $student) {
                $target = $studentGoals[$student->id] ?? 1;

                $weekBooks = $completedBooks->filter(function ($b) use ($student, $weekStart, $weekEnd) {
                    $d = Carbon::parse($b->updated_at);
                    return $b->student_id == $student->id && $d->between($weekStart, $weekEnd);
                })->count();

                if ($weekBooks >= $target) {
                    $studentsHitGoal++;
                } else {
                    $studentsMissedGoal++;
                }
            }

            // total students / hit percentage
            $totalStudents = $students->count();
            $hitPercentage = $totalStudents > 0 ? round(($studentsHitGoal / $totalStudents) * 100) : 0;

            // labels
            $weeks[] = [
                'label'      => "Week {$weekIndex}",
                'date_range' => $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
                'hit'        => $studentsHitGoal,
                'missed'     => $studentsMissedGoal,
                'percentage' => $hitPercentage,
                'class_met'  => $hitPercentage >= 75, // class met percentage
            ];

            $weekCursor->addWeek();
            $weekIndex++;
        }

        // =====================================
        // GENRES BREAKDOWN
        $genresCount = DB::table('book_student')
            ->join('books', 'books.id', '=', 'book_student.book_id')
            ->join('book_genre', 'book_genre.book_id', '=', 'books.id')
            ->join('genres', 'genres.id', '=', 'book_genre.genre_id')
            ->whereIn('book_student.student_id', $studentIds)
            ->where('book_student.status', 'completed')
            ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
            ->select('genres.name', DB::raw('COUNT(*) as count'))
            ->groupBy('genres.name')
            ->orderByDesc('count')
            ->limit(8)
            ->pluck('count', 'name')
            ->toArray();

        // =====================================
        // PHONICS BREAKDOWN
        $showPhonics = $students->contains(fn($s) => $s->level > 0 && $s->level < 8);
        $phonicsCount = [];

        if ($showPhonics) {
            $phonicsCount = DB::table('book_student')
                ->join('books', 'books.id', '=', 'book_student.book_id')
                ->join('book_phonic', 'book_phonic.book_id', '=', 'books.id')
                ->join('phonics', 'phonics.id', '=', 'book_phonic.phonic_id')
                ->whereIn('book_student.student_id', $studentIds)
                ->where('book_student.status', 'completed')
                ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
                ->select('phonics.sound', DB::raw('COUNT(*) as count'))
                ->groupBy('phonics.sound')
                ->orderByDesc('count')
                ->limit(10)
                ->pluck('count', 'sound')
                ->toArray();
        }

        return compact(
            'archive',
            'students',
            'studentsWithCounts',
            'topReaders',
            'needsEncouragement',
            'totalBooks',
            'avgDifficulty',
            'avgDifficultyScore',
            'avgRating',
            'totalReviews',
            'chartData',
            'levelDistribution',
            'avgLevel',
            'weeks',
            'genresCount',
            'showPhonics',
            'phonicsCount',
            'academicStart',
            'academicEnd'
        );
    }

    // Export classroom statistics as CSV
    public function exportStatistics($id)
    {
        // get classroom
        $classroom = Classroom::findOrFail($id);

        // verify ownership
        if ($classroom->teacher_id !== auth()->id()) {
            abort(403, "Unauthorised action");
        }

        // get students
        $studentIds = DB::table('classroom_student')
            ->where('classroom_id', $classroom->id)
            ->distinct()
            ->pluck('student_id')
            ->toArray();

        // if empty
        if (empty($studentIds)) {
            return redirect()->back()->with('error', 'No data to export');
        }

        // academic start/end
        $academicStart = (int)($classroom->academic_start ?? (now()->year - 1));
        $academicEnd   = (int)($classroom->academic_end ?? now()->year);
        $yearStart     = Carbon::create($academicStart, 9, 1)->startOfDay();
        $yearEnd       = Carbon::create($academicEnd, 8, 31)->endOfDay();

        // check for archive
        $archive = DB::table('archive_classrooms')
            ->where('classroom_id', $classroom->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // if classroom is archived, limit months for charts
        if (!$classroom->active && $archive && $archive->created_at) {
            $archiveDate = Carbon::parse($archive->created_at)->endOfWeek();
            if ($archiveDate->lt($yearEnd)) {
                $yearEnd = $archiveDate;
            }
        }

        // get all students
        $students = Student::whereIn('id', $studentIds)->get();

        // completed books
        $completedBooks = DB::table('book_student')
            ->whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$yearStart, $yearEnd])
            ->get();

        // book reviews
        $reviews = DB::table('book_reviews')
            ->whereIn('student_id', $studentIds)
            ->whereBetween('updated_at', [$yearStart, $yearEnd])
            ->get();

        // book/review counts and average rating
        $studentBookCounts   = $completedBooks->groupBy('student_id')->map->count();
        $studentReviewCounts = $reviews->groupBy('student_id')->map->count();
        $studentAvgRatings   = $reviews->whereNotNull('rating')
            ->groupBy('student_id')
            ->map(fn($r) => round($r->avg('rating'), 1));

        $filename = 'Classroom-' . preg_replace('/[^A-Za-z0-9]/', '_', $classroom->name)
            . '-' . $academicStart . '-' . $academicEnd . '-Stats.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use (
            $classroom, $students, $completedBooks, $reviews,
            $studentBookCounts, $studentReviewCounts, $studentAvgRatings,
            $academicStart, $academicEnd, $yearStart, $yearEnd
        ) {
            // file output
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // =====================================
            // CLASSROOM STATS
            fputcsv($file, ['CLASSROOM STATISTICS']);
            fputcsv($file, ['Classroom', $classroom->name]);
            fputcsv($file, ['Year Group', 'Year ' . $classroom->year_group]);
            fputcsv($file, ['Academic Year', $academicStart . ' to ' . $academicEnd]);
            fputcsv($file, ['Total Students', $students->count()]);
            fputcsv($file, ['Total Books Read', $completedBooks->count()]);
            fputcsv($file, ['Total Reviews', $reviews->whereNotNull('rating')->count()]);
            fputcsv($file, ['Average Rating', round($reviews->whereNotNull('rating')->avg('rating') ?? 0, 1)]);

            $avgDifficulty = $reviews->whereNotNull('difficulty')
                ->groupBy('difficulty')->map->count()->sortDesc()->keys()->first() ?? 'N/A';
            fputcsv($file, ['Most Common Difficulty', $avgDifficulty]);
            fputcsv($file, []);

            // =====================================
            // STUDENT SUMMARY
            fputcsv($file, ['STUDENT SUMMARY']);
            fputcsv($file, ['First Name', 'Last Name', 'Reading Level', 'Books Read', 'Reviews Written', 'Average Rating Given']);

            foreach ($students->sortByDesc(fn($s) => $studentBookCounts[$s->id] ?? 0) as $s) {
                fputcsv($file, [
                    $s->first_name,
                    $s->last_name,
                    $s->level,
                    $studentBookCounts[$s->id] ?? 0,
                    $studentReviewCounts[$s->id] ?? 0,
                    $studentAvgRatings[$s->id] ?? 'N/A',
                ]);
            }
            fputcsv($file, []);

            // =====================================
            // READING LEVEL DISTRIBUTION
            fputcsv($file, ['READING LEVEL DISTRIBUTION']);
            fputcsv($file, ['Level', 'Number of Students']);
            $levelDist = $students->groupBy('level')->map->count()->sortKeys();
            foreach ($levelDist as $level => $count) {
                fputcsv($file, ['Level ' . $level, $count]);
            }
            fputcsv($file, []);

            // =====================================
            // MONTHLY READING ACTIVITY
            fputcsv($file, ['MONTHLY READING ACTIVITY']);
            fputcsv($file, ['Month', 'Books Finished']);
            $cursor = $yearStart->copy();
            while ($cursor->lte($yearEnd)) {
                $count = $completedBooks->filter(function ($b) use ($cursor) {
                    $d = Carbon::parse($b->updated_at);
                    return $d->year === $cursor->year && $d->month === $cursor->month;
                })->count();
                fputcsv($file, [$cursor->format('M Y'), $count]);
                $cursor->addMonth();
            }
            fputcsv($file, []);

            // =====================================
            // GENRES EXPLORED
            fputcsv($file, ['GENRES EXPLORED']);
            fputcsv($file, ['Genre', 'Books Read']);
            $genres = DB::table('book_student')
                ->join('books', 'books.id', '=', 'book_student.book_id')
                ->join('book_genre', 'book_genre.book_id', '=', 'books.id')
                ->join('genres', 'genres.id', '=', 'book_genre.genre_id')
                ->whereIn('book_student.student_id', $students->pluck('id'))
                ->where('book_student.status', 'completed')
                ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
                ->select('genres.name', DB::raw('COUNT(*) as count'))
                ->groupBy('genres.name')
                ->orderByDesc('count')
                ->get();

            foreach ($genres as $g) {
                fputcsv($file, [$g->name, $g->count]);
            }
            fputcsv($file, []);

            $hasPhonicsReaders = $students->contains(fn($s) => $s->level > 0 && $s->level < 8);

            // =====================================
            // PHONICS EXPLORED
            if ($hasPhonicsReaders) {
                fputcsv($file, ['PHONICS EXPLORED (Levels 1-7)']);
                fputcsv($file, ['Phonic Sound', 'Books Read']);

                $phonics = DB::table('book_student')
                    ->join('books', 'books.id', '=', 'book_student.book_id')
                    ->join('book_phonic', 'book_phonic.book_id', '=', 'books.id')
                    ->join('phonics', 'phonics.id', '=', 'book_phonic.phonic_id')
                    ->whereIn('book_student.student_id', $students->pluck('id'))
                    ->where('book_student.status', 'completed')
                    ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
                    ->select('phonics.sound', DB::raw('COUNT(*) as count'))
                    ->groupBy('phonics.sound')
                    ->orderByDesc('count')
                    ->get();

                if ($phonics->isEmpty()) {
                    fputcsv($file, ['No phonics data available', '']);
                } else {
                    foreach ($phonics as $p) {
                        fputcsv($file, [$p->sound, $p->count]);
                    }
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function ensureOwnsClassroom(Classroom $classroom): void
    {
        abort_unless($classroom->teacher_id === auth()->id(), 403);
    }

    // Get year groups for a teacher
    private function yearGroupsForTeacher(int $teacherId): array
    {
        return Classroom::query()
            ->where('teacher_id', $teacherId)
            ->withCount(['students' => fn ($q) => $q->where('classroom_student.active', 1)])
            ->orderBy('year_group')
            ->get()
            ->map(fn ($c) => [
                'year'          => "{$c->year_group}",
                'name'          => $c->name,
                'students'      => $c->students_count,
                'slug'          => $c->id,
                'active'        => $c->active,
                'academic_year' => $c->academic_year,
                'is_progressed' => $c->is_progressed,
            ])
            ->toArray();
    }

    // Class overiew
    public function classView(Classroom $classroom)
    {
        // verify ownership
        $this->ensureOwnsClassroom($classroom);

        $yearGroups = $this->yearGroupsForTeacher(auth()->id());
        $classroom->loadCount('students');

        // get announcements
        $announcements = DB::table('announcements')
            ->leftJoin('students', 'announcements.student_id', '=', 'students.id')
            ->where('announcements.classroom_id', $classroom->id)
            ->select('announcements.*', 'students.first_name', 'students.last_name')
            ->orderBy('announcements.created_at', 'desc')
            ->get();

        $stats = $this->buildClassroomStatistics($classroom);

        return view('teacher.classes.view', array_merge(
            $stats,
            compact('classroom', 'yearGroups', 'announcements')
        ));
    }

    // Delete announcements
    public function deleteAnnouncement($id)
    {
        $announcement = DB::table('announcements')->where('id', $id)->first();

        if (!$announcement) {
            abort(404);
        }

        // verify ownership
        $classroom = Classroom::findOrFail($announcement->classroom_id);
        $this->ensureOwnsClassroom($classroom);

        DB::transaction(function () use ($id) {
            // check hidden announcements too if needed
            DB::table('hidden_announcements')->where('announcement_id', $id)->delete();
            DB::table('announcements')->where('id', $id)->delete();
        });

        return back()->with('success', 'Announcement deleted.');
    }
}