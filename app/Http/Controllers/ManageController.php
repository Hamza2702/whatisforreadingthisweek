<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;

class ManageController extends Controller
{
    // Manage student view
    public function show($userId)
    {
        // get student via user id
        $student = Student::where('user_id', $userId)->with(['user'])->firstOrFail();

        // verify ownership
        $classroom = Classroom::findOrFail($student->classroom_id);
        abort_unless($classroom->teacher_id === auth()->id(), 403, 'Unauthorised action');

        // get yeargroups/stats method
        $yearGroups = $this->yearGroupsForTeacher(auth()->id());
        $stats = $this->buildStudentStatistics($student, $classroom);

        return view('teacher.classes.manage', array_merge(
            $stats,
            compact('student', 'classroom', 'yearGroups')
        ));
    }

    // Build individual student stat data
    private function buildStudentStatistics(Student $student, Classroom $classroom): array
    {
        // academic start/end
        $academicStart = (int)($classroom->academic_start ?? (now()->year - 1));
        $academicEnd   = (int)($classroom->academic_end ?? now()->year);
        $yearStart     = Carbon::create($academicStart, 9, 1)->startOfDay();
        $yearEnd       = Carbon::create($academicEnd, 8, 31)->endOfDay();

        // for active classrooms, limit chart to now for no empty future months
        if ($classroom->active) {
            $now = now()->endOfWeek();
            if ($now->lt($yearEnd)) {
                $yearEnd = $now;
            }
        }

        // =====================================
        // COMPLETED BOOKS
        $completedBooks = DB::table('book_student')
            ->join('books', 'books.id', '=', 'book_student.book_id')
            ->where('book_student.student_id', $student->id)
            ->where('book_student.status', 'completed')
            ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
            ->select(
                'books.id',
                'books.title',
                'books.author',
                'books.cover_id',
                'books.ort_level',
                'books.ort_colour',
                'book_student.updated_at as finished_at',
                'book_student.status'
            )
            ->orderByDesc('book_student.updated_at')
            ->get();

        $totalBooks = $completedBooks->count();

        // =====================================
        // CURRENTLY READING
        $currentlyReading = DB::table('book_student')
            ->join('books', 'books.id', '=', 'book_student.book_id')
            ->where('book_student.student_id', $student->id)
            ->where('book_student.status', 'reading')
            ->select('books.*', 'book_student.updated_at as started_at')
            ->orderByDesc('book_student.updated_at')
            ->get();

        // =====================================
        // RATING/DIFFICULTY
        // book reviews
        $reviews = DB::table('book_reviews')
            ->join('books', 'books.id', '=', 'book_reviews.book_id')
            ->where('book_reviews.student_id', $student->id)
            ->whereBetween('book_reviews.updated_at', [$yearStart, $yearEnd])
            ->select(
                'book_reviews.*',
                'books.title as book_title'
            )
            ->orderByDesc('book_reviews.updated_at')
            ->get();

        // get average rating, reviews and difficulty
        $totalReviews = $reviews->whereNotNull('rating')->count();
        $avgRating    = round($reviews->whereNotNull('rating')->avg('rating') ?? 0, 1);

        $avgDifficulty = $reviews->whereNotNull('difficulty')
            ->groupBy('difficulty')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first() ?? 'N/A';

        // =====================================
        // MONTHLY ACTIVITY READING CHART DATA
        $chartData = [];
        $cursor = $yearStart->copy();
        while ($cursor->lte($yearEnd)) {
            $monthLabel = $cursor->format('M Y');
            $chartData[$monthLabel] = $completedBooks->filter(function ($b) use ($cursor) {
                $d = Carbon::parse($b->finished_at);
                return $d->year === $cursor->year && $d->month === $cursor->month;
            })->count();
            $cursor->addMonth();
        }

        // =====================================
        // WEEKLY GOAL TRACKING
        $weeklyGoalTarget = DB::table('student_weekly_goals')
            ->where('student_id', $student->id)
            ->where('classroom_id', $classroom->id)
            ->value('target') ?? 1;

        $weeks = [];
        $weekCursor = $yearStart->copy()->startOfWeek();
        $weekIndex = 1;

        while ($weekCursor->lte($yearEnd) && $weekCursor->lte(now())) {
            $weekStart = $weekCursor->copy();
            $weekEnd   = $weekCursor->copy()->endOfWeek();

            // books read this week
            $weekBooks = $completedBooks->filter(function ($b) use ($weekStart, $weekEnd) {
                $d = Carbon::parse($b->finished_at);
                return $d->between($weekStart, $weekEnd);
            })->count();

            // hit or miss goal / percentage
            $hitGoal = $weekBooks >= $weeklyGoalTarget;
            $percentage = $weeklyGoalTarget > 0
                ? min(100, round(($weekBooks / $weeklyGoalTarget) * 100))
                : 0;

            // labels
            $weeks[] = [
                'label'      => "Week {$weekIndex}",
                'date_range' => $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
                'books'      => $weekBooks,
                'target'     => $weeklyGoalTarget,
                'percentage' => $percentage,
                'hit_goal'   => $hitGoal,
            ];

            $weekCursor->addWeek();
            $weekIndex++;
        }

        // =====================================
        // READING STREAK
        $streak = DB::table('student_streaks')
            ->where('student_id', $student->id)
            ->value('streak_count') ?? 0;

        // =====================================
        // GENRES BREAKDOWN
        $genresCount = DB::table('book_student')
            ->join('books', 'books.id', '=', 'book_student.book_id')
            ->join('book_genre', 'book_genre.book_id', '=', 'books.id')
            ->join('genres', 'genres.id', '=', 'book_genre.genre_id')
            ->where('book_student.student_id', $student->id)
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
        $showPhonics  = $student->level > 0 && $student->level < 8;
        $phonicsCount = [];

        if ($showPhonics) {
            $phonicsCount = DB::table('book_student')
                ->join('books', 'books.id', '=', 'book_student.book_id')
                ->join('book_phonic', 'book_phonic.book_id', '=', 'books.id')
                ->join('phonics', 'phonics.id', '=', 'book_phonic.phonic_id')
                ->where('book_student.student_id', $student->id)
                ->where('book_student.status', 'completed')
                ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
                ->select('phonics.sound', DB::raw('COUNT(*) as count'))
                ->groupBy('phonics.sound')
                ->orderByDesc('count')
                ->pluck('count', 'sound')
                ->toArray();
        }

        // =====================================
        // FAVOURITE BOOKS
        $favouriteBooks = DB::table('student_favourite_books')
            ->join('books', 'books.id', '=', 'student_favourite_books.book_id')
            ->where('student_favourite_books.student_id', $student->id)
            ->select('books.*')
            ->get();

        // =====================================
        // LEADERBOARD POSITION of classroom
        $classmateBookCounts = DB::table('book_student')
            ->join('classroom_student', 'classroom_student.student_id', '=', 'book_student.student_id')
            ->where('classroom_student.classroom_id', $classroom->id)
            ->where('book_student.status', 'completed')
            ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
            ->select('book_student.student_id', DB::raw('COUNT(*) as total'))
            ->groupBy('book_student.student_id')
            ->orderByDesc('total')
            ->pluck('total', 'book_student.student_id');

        // class rank / size
        $classRank = $classmateBookCounts->keys()->search($student->id);
        $classRank = $classRank === false ? null : $classRank + 1;
        $classSize = $classmateBookCounts->count();

        return compact(
            'academicStart',
            'academicEnd',
            'totalBooks',
            'totalReviews',
            'avgRating',
            'avgDifficulty',
            'completedBooks',
            'currentlyReading',
            'reviews',
            'chartData',
            'weeklyGoalTarget',
            'weeks',
            'streak',
            'genresCount',
            'showPhonics',
            'phonicsCount',
            'favouriteBooks',
            'classRank',
            'classSize'
        );
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

    // Teacher must own the classroom
    private function ensureOwnsClassroom(Classroom $classroom): void
    {
        abort_unless($classroom->teacher_id === auth()->id(), 403);
    }

    // Update student account details
    public function updateField(Request $request, $userId)
    {
        // get student
        $student = Student::where('user_id', $userId)->with('user')->firstOrFail();

        // find the student's active classroom
        $classroom = $student->classrooms()
            ->wherePivot('active', true)
            ->first();

        // verify ownership
        abort_unless($classroom !== null, 404, 'Student is not in a classroom');
        $this->ensureOwnsClassroom($classroom);

        $field = $request->input('field');

        switch ($field) {
            // reading level
            case 'level':
                $data = $request->validate([
                    'value' => 'required|integer|min:1|max:20',
                ]);
                $student->update(['level' => (int) $data['value']]);
                return response()->json([
                    'success' => true,
                    'value'   => $student->level,
                    'message' => 'Reading level updated',
                ]);

            // email
            case 'email':
                $data = $request->validate([
                    'value' => 'required|email|max:255|unique:users,email,' . $student->user->id,
                ]);
                $student->user->update(['email' => $data['value']]);
                return response()->json([
                    'success' => true,
                    'value'   => $student->user->email,
                    'message' => 'Email updated',
                ]);

            // password
            case 'password':
                $data = $request->validate([
                    'value' => [
                        'required',
                        'confirmed',
                        Password::min(8)->mixedCase()->numbers()->symbols(),
                    ],
                    'value_confirmation' => 'required',
                ], [
                    'value.confirmed' => 'The passwords do not match.',
                    'value.min'       => 'Password must be at least 8 characters.',
                    'value.mixed'     => 'Password must include uppercase and lowercase letters.',
                    'value.numbers'   => 'Password must include at least one number.',
                    'value.symbols'   => 'Password must include at least one symbol.',
                ]);
                $student->user->update(['password' => bcrypt($data['value'])]);
                return response()->json([
                    'success' => true,
                    'value'   => '••••••••',
                    'message' => 'Password updated',
                ]);

            // weekly goal
            case 'weekly_goal':
                $data = $request->validate([
                    'value' => 'required|integer|min:1|max:3',
                ]);

                // update
                $rows = DB::table('student_weekly_goals')
                    ->where('student_id', $student->id)
                    ->where('classroom_id', $student->classroom_id)
                    ->update([
                        'target'     => (int) $data['value'],
                        'updated_at' => now(),
                    ]);

                // create if none
                if ($rows === 0) {
                    DB::table('student_weekly_goals')->insert([
                        'school_id'    => $student->school_id,
                        'classroom_id' => $student->classroom_id,
                        'student_id'   => $student->id,
                        'target'       => (int) $data['value'],
                        'created_at'   => now(),
                        'updated_at'   => now(),
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'value'   => (int) $data['value'],
                    'message' => 'Weekly goal updated',
                ]);

            // invalid field name
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid field',
                ], 422);
        }
    }

    // Export student statistics as CSV
    public function exportStatistics($userId)
    {
        // get student
        $student = Student::where('user_id', $userId)->with(['user'])->firstOrFail();

        // verify ownership
        $classroom = Classroom::findOrFail($student->classroom_id);
        if ($classroom->teacher_id !== auth()->id()) {
            abort(403, 'Unauthorised action');
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
        } elseif ($classroom->active) {
            // for active classrooms, limit chart to now for no empty future months
            $now = now()->endOfWeek();
            if ($now->lt($yearEnd)) {
                $yearEnd = $now;
            }
        }

        // completed books
        $completedBooks = DB::table('book_student')
            ->join('books', 'books.id', '=', 'book_student.book_id')
            ->where('book_student.student_id', $student->id)
            ->where('book_student.status', 'completed')
            ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
            ->select(
                'books.title',
                'books.author',
                'book_student.updated_at as finished_at'
            )
            ->orderByDesc('book_student.updated_at')
            ->get();

        // currently reading
        $currentlyReading = DB::table('book_student')
            ->join('books', 'books.id', '=', 'book_student.book_id')
            ->where('book_student.student_id', $student->id)
            ->where('book_student.status', 'reading')
            ->select('books.title', 'books.author', 'book_student.updated_at as started_at')
            ->orderByDesc('book_student.updated_at')
            ->get();

        // book reviews
        $reviews = DB::table('book_reviews')
            ->join('books', 'books.id', '=', 'book_reviews.book_id')
            ->where('book_reviews.student_id', $student->id)
            ->whereBetween('book_reviews.updated_at', [$yearStart, $yearEnd])
            ->select(
                'book_reviews.title as review_title',
                'book_reviews.description as review_body',
                'book_reviews.rating',
                'book_reviews.difficulty',
                'book_reviews.updated_at as review_date',
                'books.title as book_title'
            )
            ->orderByDesc('book_reviews.updated_at')
            ->get();

        // review stats
        $totalReviews  = $reviews->whereNotNull('rating')->count();
        $avgRating     = round($reviews->whereNotNull('rating')->avg('rating') ?? 0, 1);
        $avgDifficulty = $reviews->whereNotNull('difficulty')
            ->groupBy('difficulty')
            ->map->count()
            ->sortDesc()
            ->keys()
            ->first() ?? 'N/A';

        // weekly goal target
        $weeklyGoalTarget = DB::table('student_weekly_goals')
            ->where('student_id', $student->id)
            ->where('classroom_id', $classroom->id)
            ->value('target') ?? 1;

        // reading streak
        $streak = DB::table('student_streaks')
            ->where('student_id', $student->id)
            ->value('streak_count') ?? 0;

        // leaderboard position
        $classmateBookCounts = DB::table('book_student')
            ->join('classroom_student', 'classroom_student.student_id', '=', 'book_student.student_id')
            ->where('classroom_student.classroom_id', $classroom->id)
            ->where('book_student.status', 'completed')
            ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
            ->select('book_student.student_id', DB::raw('COUNT(*) as total'))
            ->groupBy('book_student.student_id')
            ->orderByDesc('total')
            ->pluck('total', 'book_student.student_id');

        // class rank / size
        $classRank = $classmateBookCounts->keys()->search($student->id);
        $classRank = $classRank === false ? null : $classRank + 1;
        $classSize = $classmateBookCounts->count();

        // build filename
        $studentSlug = preg_replace('/[^A-Za-z0-9]/', '_', $student->first_name . '_' . $student->last_name);
        $filename = 'Student-' . $studentSlug
            . '-' . $academicStart . '-' . $academicEnd . '-Stats.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ];

        $callback = function () use (
            $student, $classroom, $completedBooks, $currentlyReading, $reviews,
            $totalReviews, $avgRating, $avgDifficulty, $weeklyGoalTarget,
            $streak, $classRank, $classSize,
            $academicStart, $academicEnd, $yearStart, $yearEnd
        ) {
            // file output
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // =====================================
            // STUDENT STATS
            fputcsv($file, ['STUDENT STATISTICS']);
            fputcsv($file, ['First Name', $student->first_name]);
            fputcsv($file, ['Last Name', $student->last_name]);
            fputcsv($file, ['Reading Level', $student->level]);
            fputcsv($file, ['Classroom', $classroom->name]);
            fputcsv($file, ['Year Group', 'Year ' . $classroom->year_group]);
            fputcsv($file, ['Academic Year', $academicStart . ' to ' . $academicEnd]);
            fputcsv($file, ['Total Books Read', $completedBooks->count()]);
            fputcsv($file, ['Total Reviews', $totalReviews]);
            fputcsv($file, ['Average Rating Given', $avgRating]);
            fputcsv($file, ['Most Common Difficulty', $avgDifficulty]);
            fputcsv($file, ['Weekly Goal (books/week)', $weeklyGoalTarget]);
            fputcsv($file, ['Current Day Streak', $streak]);
            fputcsv($file, ['Class Rank', $classRank ? $classRank . ' of ' . $classSize : 'N/A']);
            fputcsv($file, []);

            // =====================================
            // READING HISTORY
            fputcsv($file, ['READING HISTORY']);
            fputcsv($file, ['Title', 'Author', 'Date Finished']);
            if ($completedBooks->isEmpty()) {
                fputcsv($file, ['No books completed in this period', '', '']);
            } else {
                foreach ($completedBooks as $b) {
                    fputcsv($file, [
                        $b->title,
                        $b->author,
                        Carbon::parse($b->finished_at)->format('M j, Y'),
                    ]);
                }
            }
            fputcsv($file, []);

            // =====================================
            // CURRENTLY READING
            fputcsv($file, ['CURRENTLY READING']);
            fputcsv($file, ['Title', 'Author', 'Date Started']);
            if ($currentlyReading->isEmpty()) {
                fputcsv($file, ['No books in progress', '', '']);
            } else {
                foreach ($currentlyReading as $b) {
                    fputcsv($file, [
                        $b->title,
                        $b->author,
                        Carbon::parse($b->started_at)->format('M j, Y'),
                    ]);
                }
            }
            fputcsv($file, []);

            // =====================================
            // REVIEWS WRITTEN
            fputcsv($file, ['REVIEWS WRITTEN']);
            fputcsv($file, ['Book Title', 'Review Title', 'Review Description', 'Rating', 'Difficulty', 'Date']);
            if ($reviews->isEmpty()) {
                fputcsv($file, ['No reviews in this period', '', '', '', '', '']);
            } else {
                foreach ($reviews as $r) {
                    fputcsv($file, [
                        $r->book_title,
                        $r->review_title ?? '',
                        $r->review_body ?? '',
                        $r->rating !== null ? $r->rating . '/5' : 'N/A',
                        $r->difficulty ?? 'N/A',
                        Carbon::parse($r->review_date)->format('M j, Y'),
                    ]);
                }
            }
            fputcsv($file, []);

            // =====================================
            // MONTHLY READING ACTIVITY
            fputcsv($file, ['MONTHLY READING ACTIVITY']);
            fputcsv($file, ['Month', 'Books Finished']);
            $cursor = $yearStart->copy();
            while ($cursor->lte($yearEnd)) {
                $count = $completedBooks->filter(function ($b) use ($cursor) {
                    $d = Carbon::parse($b->finished_at);
                    return $d->year === $cursor->year && $d->month === $cursor->month;
                })->count();
                fputcsv($file, [$cursor->format('M Y'), $count]);
                $cursor->addMonth();
            }
            fputcsv($file, []);

            // =====================================
            // WEEKLY GOAL PERFORMANCE
            fputcsv($file, ['WEEKLY GOAL PERFORMANCE']);
            fputcsv($file, ['Week', 'Date Range', 'Books Read', 'Target', 'Percentage', 'Hit Goal?']);
            $weekCursor = $yearStart->copy()->startOfWeek();
            $weekIndex = 1;
            while ($weekCursor->lte($yearEnd) && $weekCursor->lte(now())) {
                $weekStart = $weekCursor->copy();
                $weekEnd   = $weekCursor->copy()->endOfWeek();

                // books read this week
                $weekBooks = $completedBooks->filter(function ($b) use ($weekStart, $weekEnd) {
                    $d = Carbon::parse($b->finished_at);
                    return $d->between($weekStart, $weekEnd);
                })->count();

                // hit or miss goal / percentage
                $percentage = $weeklyGoalTarget > 0
                    ? min(100, round(($weekBooks / $weeklyGoalTarget) * 100))
                    : 0;
                $hitGoal = $weekBooks >= $weeklyGoalTarget ? 'Yes' : 'No';

                fputcsv($file, [
                    "Week {$weekIndex}",
                    $weekStart->format('M j') . ' - ' . $weekEnd->format('M j'),
                    $weekBooks,
                    $weeklyGoalTarget,
                    $percentage . '%',
                    $hitGoal,
                ]);

                $weekCursor->addWeek();
                $weekIndex++;
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
                ->where('book_student.student_id', $student->id)
                ->where('book_student.status', 'completed')
                ->whereBetween('book_student.updated_at', [$yearStart, $yearEnd])
                ->select('genres.name', DB::raw('COUNT(*) as count'))
                ->groupBy('genres.name')
                ->orderByDesc('count')
                ->get();

            if ($genres->isEmpty()) {
                fputcsv($file, ['No genre data available', '']);
            } else {
                foreach ($genres as $g) {
                    fputcsv($file, [$g->name, $g->count]);
                }
            }
            fputcsv($file, []);

            // =====================================
            // PHONICS EXPLORED
            if ($student->level > 0 && $student->level < 8) {
                fputcsv($file, ['PHONICS EXPLORED (Levels 1-7)']);
                fputcsv($file, ['Phonic Sound', 'Books Read']);

                $phonics = DB::table('book_student')
                    ->join('books', 'books.id', '=', 'book_student.book_id')
                    ->join('book_phonic', 'book_phonic.book_id', '=', 'books.id')
                    ->join('phonics', 'phonics.id', '=', 'book_phonic.phonic_id')
                    ->where('book_student.student_id', $student->id)
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
}