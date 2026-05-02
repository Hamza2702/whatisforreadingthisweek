<?php

namespace Tests\Feature;

use App\Http\Controllers\ReadingController;
use App\Models\Classroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReadingControllerTest extends TestCase
{
    use RefreshDatabase;

    private ReadingController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new ReadingController();
        // cache mentioned??!
        Cache::flush();
    }

    // allow private methods to be tested
    private function callPrivateMethod(string $methodName, array $arguments = [])
    {
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($this->controller, $arguments);
    }

    // create test school
    private function createSchool(): int
    {
        return DB::table('schools')->insertGetId([
            'urn' => fake()->unique()->numerify('######'),
            'name' => 'Test School',
            'town' => 'Test Town',
            'postcode' => 'TE1 1ST',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // create test user
    private function createUser(int $schoolId, string $role = 'student'): int
    {
        return DB::table('users')->insertGetId([
            'name' => fake()->name(),
            'username' => fake()->unique()->userName(),
            'phone' => '07123456789',
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'school_id' => $schoolId,
            'remember_token' => null,
            'isAdmin' => $role === 'admin' ? 1 : 0,
            'role' => $role,
            'pfp' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // create test classroom
    private function createClassroom(int $schoolId, ?int $teacherId = null): int
    {
        $teacherId = $teacherId ?: $this->createUser($schoolId, 'teacher');

        return DB::table('classrooms')->insertGetId([
            'school_id' => $schoolId,
            'teacher_id' => $teacherId,
            'name' => 'Test Class',
            'year_group' => 5,
            'stage' => 'KS2',
            'academic_year' => '2025/2026',
            'academic_start' => 2025,
            'academic_end' => 2026,
            'active' => 1,
            'is_progressed' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // create student
    private function createStudent(int $schoolId, int $classroomId, int $level = 2, bool $attachToClassroom = true): int {
        $userId = $this->createUser($schoolId, 'student');

        // assign to db
        $studentId = DB::table('students')->insertGetId([
            'school_id' => $schoolId,
            'user_id' => $userId,
            'classroom_id' => $classroomId,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'level' => $level,
            'date_of_birth' => '2014-01-01',
            'active' => 1,
            'is_exceptional' => 0,
            'pfp' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // add to classroom
        if ($attachToClassroom) {
            DB::table('classroom_student')->insert([
                'school_id' => $schoolId,
                'classroom_id' => $classroomId,
                'student_id' => $studentId,
                'starts_on' => now()->toDateString(),
                'ends_on' => null,
                'active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // give weekly goals
        DB::table('student_weekly_goals')->insert([
            'school_id' => $schoolId,
            'classroom_id' => $classroomId,
            'student_id' => $studentId,
            'target' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $studentId;
    }

    // create book
    private function createBook(int $ortLevel = 2, string $ortColour = 'Red', ?string $title = null): int {
        return DB::table('books')->insertGetId([
            'ol_key' => fake()->unique()->bothify('BOOK-####-????'),
            'title' => $title ?: fake()->sentence(3),
            'author' => fake()->name(),
            'cover_id' => 'Ba1fEQAAQBAJ',
            'ort_level' => $ortLevel,
            'ort_colour' => $ortColour,
            'description' => fake()->paragraph(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // create book stock
    private function createBookStock(int $schoolId, int $bookId, int $stock): void
    {
        DB::table('book_school_stocks')->insert([
            'book_id' => $bookId,
            'school_id' => $schoolId,
            'stock' => $stock,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // create book review
    private function createBookReview(int $schoolId, int $studentId, int $bookId, string $difficulty): void {
        DB::table('book_reviews')->insert([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'book_id' => $bookId,
            'rating' => 3,
            'difficulty' => $difficulty,
            'title' => 'Test Review',
            'description' => 'Test review description.',
            'upvotes' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // create multiple 'real' students adn reviews
    private function insertDifficultyReviews(int $schoolId, int $classroomId, int $bookId, array $difficultyCounts): void {
        foreach ($difficultyCounts as $difficulty => $count) {
            for ($i = 0; $i < $count; $i++) {
                $studentId = $this->createStudent(
                    schoolId: $schoolId,
                    classroomId: $classroomId,
                    level: 2,
                    attachToClassroom: false
                );

                $this->createBookReview(
                    schoolId: $schoolId,
                    studentId: $studentId,
                    bookId: $bookId,
                    difficulty: $difficulty
                );
            }
        }
    }

    // test get ORT colours
    public function test_get_oxford_colour_maps_all_levels_from_zero_to_twenty(): void
    {
        $expectedColours = [
            0 => 'Light Purple',
            1 => 'Pink',
            2 => 'Red',
            3 => 'Yellow',
            4 => 'Light Blue',
            5 => 'Green',
            6 => 'Orange',
            7 => 'Turquoise',
            8 => 'Purple',
            9 => 'Gold',
            10 => 'White',
            11 => 'Lime',
            12 => 'Lime+',
            13 => 'Grey',
            14 => 'Grey',
            15 => 'Dark Blue',
            16 => 'Dark Blue',
            17 => 'Dark Red',
            18 => 'Dark Red',
            19 => 'Dark Red',
            20 => 'Dark Red',
        ];

        foreach ($expectedColours as $level => $expectedColour) {
            $this->assertEquals(
                $expectedColour,
                $this->callPrivateMethod('getOxfordColour', [$level]),
                "ORT level {$level} should map to {$expectedColour}."
            );
        }
    }

    // test get ORT colours dark red for invalid boundaries
    public function test_get_oxford_colour_returns_dark_red_for_invalid_numeric_levels(): void
    {
        $this->assertEquals('Dark Red', $this->callPrivateMethod('getOxfordColour', [-1]));
        $this->assertEquals('Dark Red', $this->callPrivateMethod('getOxfordColour', [21]));
        $this->assertEquals('Dark Red', $this->callPrivateMethod('getOxfordColour', [99]));
    }

    // test calculate difficulty bias with no reviews
    public function test_calculate_difficulty_bias_returns_zero_when_no_reviews_exist(): void
    {
        $reviews = collect();
        $result = $this->callPrivateMethod('calculateDifficultyBias', [$reviews]);
        $this->assertEquals(0, $result);
    }

    // test calculate difficulty bias .5 threshold
    public function test_calculate_difficulty_bias_returns_positive_at_plus_zero_point_five_threshold(): void
    {
        $reviews = collect([
            (object) ['difficulty' => 'easy'],
            (object) ['difficulty' => 'okay'],
        ]);

        /*
         * easy = +1
         * okay = 0
         * average = 1 / 2 = +0.5
         */
        $result = $this->callPrivateMethod('calculateDifficultyBias', [$reviews]);
        $this->assertEquals(1, $result);
    }

    // test calculate difficulty bias -0.5 threshold
    public function test_calculate_difficulty_bias_returns_negative_at_minus_zero_point_five_threshold(): void
    {
        $reviews = collect([
            (object) ['difficulty' => 'hard'],
            (object) ['difficulty' => 'okay'],
        ]);

        /*
         * hard = -1
         * okay = 0
         * average = -1 / 2 = -0.5
         */
        $result = $this->callPrivateMethod('calculateDifficultyBias', [$reviews]);
        $this->assertEquals(-1, $result);
    }

    // test calculate difficulty bias balanced reviews
    public function test_calculate_difficulty_bias_returns_zero_for_balanced_reviews(): void
    {
        $reviews = collect([
            (object) ['difficulty' => 'easy'],
            (object) ['difficulty' => 'hard'],
            (object) ['difficulty' => 'okay'],
        ]);

        $result = $this->callPrivateMethod('calculateDifficultyBias', [$reviews]);
        $this->assertEquals(0, $result);
    }

    // test map bias peer difficulty
    public function test_map_bias_to_peer_difficulty(): void
    {
        $this->assertEquals('easy', $this->callPrivateMethod('mapBiasToPeerDifficulty', [-1]));
        $this->assertEquals('okay', $this->callPrivateMethod('mapBiasToPeerDifficulty', [0]));
        $this->assertEquals('hard', $this->callPrivateMethod('mapBiasToPeerDifficulty', [1]));
    }

    // test compute book difficulty no reviews
    public function test_compute_book_difficulty_map_returns_empty_array_when_no_reviews_exist(): void
    {
        $result = $this->callPrivateMethod('computeBookDifficultyMap');
        $this->assertEquals([], $result);
    }

    // test compute book difficulty <3 reviews - heart :)
    public function test_compute_book_difficulty_map_ignores_books_with_fewer_than_three_reviews(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $bookId = $this->createBook();

        $this->insertDifficultyReviews($schoolId, $classroomId, $bookId, [
            'easy' => 2,
        ]);

        $result = $this->callPrivateMethod('computeBookDifficultyMap');
        $this->assertArrayNotHasKey($bookId, $result);
    }

    // test compute book difficulty classifies easy books
    public function test_compute_book_difficulty_map_classifies_easy_books(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $bookId = $this->createBook();

        $this->insertDifficultyReviews($schoolId, $classroomId, $bookId, [
            'easy' => 3,
        ]);

        $result = $this->callPrivateMethod('computeBookDifficultyMap');
        $this->assertEquals('easy', $result[$bookId]);
    }

    // test compute book difficulty classifies okay books
    public function test_compute_book_difficulty_map_classifies_okay_books(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $bookId = $this->createBook();

        $this->insertDifficultyReviews($schoolId, $classroomId, $bookId, [
            'okay' => 3,
        ]);

        $result = $this->callPrivateMethod('computeBookDifficultyMap');
        $this->assertEquals('okay', $result[$bookId]);
    }

    // test compute book difficulty classifies hard books
    public function test_compute_book_difficulty_map_classifies_hard_books(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $bookId = $this->createBook();

        $this->insertDifficultyReviews($schoolId, $classroomId, $bookId, [
            'hard' => 3,
        ]);

        $result = $this->callPrivateMethod('computeBookDifficultyMap');

        $this->assertEquals('hard', $result[$bookId]);
    }

    // test compute book difficulty classifies 1.67 easy boundary
    public function test_compute_book_difficulty_map_classifies_score_below_one_point_six_seven_as_easy(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $bookId = $this->createBook();

        // 1 easy review = 1 x 1 = 1, 2 okay reviews = 2 x 2 = 4 ===> total is 5/3 = 1.6*
        // score < 1.67 (should be easy)
        $this->insertDifficultyReviews($schoolId, $classroomId, $bookId, [
            'easy' => 1,
            'okay' => 2,
        ]);

        $result = $this->callPrivateMethod('computeBookDifficultyMap');

        $this->assertEquals('easy', $result[$bookId]);
    }

    // test compute book difficulty classifies below 1.67 easy
    public function test_compute_book_difficulty_map_classifies_one_point_six_seven_boundary_as_okay(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $bookId = $this->createBook();

        // 33 easy reviews = 33 x 1 = 33, 67 okay reviews = 67 x 2 = 134 ====> total is 167/100 = 1.67
        // if score < 1.67 = easy
        // elseif score 2.34 = okay (1.67 should be okay)
        $this->insertDifficultyReviews($schoolId, $classroomId, $bookId, [
            'easy' => 33,
            'okay' => 67,
        ]);

        $result = $this->callPrivateMethod('computeBookDifficultyMap');

        $this->assertEquals('okay', $result[$bookId]);
    }

    // test compute book difficulty classifies below 2.34 okay
    public function test_compute_book_difficulty_map_classifies_score_below_two_point_three_four_as_okay(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $bookId = $this->createBook();

        // 1 okay review = 1 x 2 = 2, 2 hard reviews = 2 x 3 = 6 ===> total is 8/3 = 2.3*
        // score < 2.34 (should be okay)
        $this->insertDifficultyReviews($schoolId, $classroomId, $bookId, [
            'okay' => 2,
            'hard' => 1,
        ]);

        $result = $this->callPrivateMethod('computeBookDifficultyMap');

        $this->assertEquals('okay', $result[$bookId]);
    }

    // test compute book difficulty classifies 2.34 hard boundary
    public function test_compute_book_difficulty_map_classifies_two_point_three_four_boundary_as_hard(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $bookId = $this->createBook();

        // 66 okay reviews = 66 x 2 = 132, 34 hard reviews = 34 x 3 = 102 ===> total is 234/100 = 2.34
        // elseif score < 2.34, okay
        // else hard (2.34 should be hard)
        $this->insertDifficultyReviews($schoolId, $classroomId, $bookId, [
            'okay' => 66,
            'hard' => 34,
        ]);

        $result = $this->callPrivateMethod('computeBookDifficultyMap');

        $this->assertEquals('hard', $result[$bookId]);
    }

    // test compute book difficulty book id filter
    public function test_compute_book_difficulty_map_can_filter_by_book_ids(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $includedBookId = $this->createBook(title: 'Included Book');
        $excludedBookId = $this->createBook(title: 'Excluded Book');

        $this->insertDifficultyReviews($schoolId, $classroomId, $includedBookId, [
            'easy' => 3,
        ]);

        $this->insertDifficultyReviews($schoolId, $classroomId, $excludedBookId, [
            'hard' => 3,
        ]);

        $result = $this->callPrivateMethod('computeBookDifficultyMap', [
            [$includedBookId],
        ]);

        $this->assertArrayHasKey($includedBookId, $result);
        $this->assertArrayNotHasKey($excludedBookId, $result);
        $this->assertEquals('easy', $result[$includedBookId]);
    }

    // test book difficulty filtered w/ specific ids
    public function test_build_book_difficulty_map_with_specific_ids_uses_filtered_computation(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $bookId = $this->createBook();

        $this->insertDifficultyReviews($schoolId, $classroomId, $bookId, [
            'hard' => 3,
        ]);

        $result = $this->callPrivateMethod('buildBookDifficultyMap', [
            [$bookId],
        ]);

        $this->assertEquals('hard', $result[$bookId]);
    }

    // test generate all cache flag
    public function test_mark_generate_all_as_used_stores_weekly_cache_flag(): void
    {
        $classroomId = 123;
        $this->callPrivateMethod('markGenerateAllAsUsed', [$classroomId]);
        $key = $this->callPrivateMethod('generateAllCacheKey', [$classroomId]);
        $this->assertTrue(Cache::has($key));
    }

    // test generate all has ran
    public function test_has_generate_all_run_this_week_returns_true_when_cache_flag_exists(): void
    {
        $classroomId = 123;
        $this->callPrivateMethod('markGenerateAllAsUsed', [$classroomId]);

        $result = $this->callPrivateMethod('hasGenerateAllRunThisWeek', [
            $classroomId,
            [1, 2, 3],
        ]);

        $this->assertTrue($result);
    }

    // test generate all cache key change
    public function test_generate_all_cache_key_changes_between_weeks(): void
    {
        $this->travelTo(now()->startOfWeek());
        $weekOneKey = $this->callPrivateMethod('generateAllCacheKey', [10]);
        $this->travelTo(now()->addWeek());
        $weekTwoKey = $this->callPrivateMethod('generateAllCacheKey', [10]);
        $this->assertNotEquals($weekOneKey, $weekTwoKey);
    }

    // test generate all reset new week
    public function test_generate_all_lock_resets_in_new_week_when_using_weekly_cache_key(): void
    {
        $this->travelTo(now()->startOfWeek());
        $classroomId = 200;
        $this->callPrivateMethod('markGenerateAllAsUsed', [$classroomId]);

        $thisWeekResult = $this->callPrivateMethod('hasGenerateAllRunThisWeek', [
            $classroomId,
            [1, 2, 3],
        ]);

        $this->assertTrue($thisWeekResult);
        $this->travelTo(now()->addWeek());

        $nextWeekResult = $this->callPrivateMethod('hasGenerateAllRunThisWeek', [
            $classroomId,
            [1, 2, 3],
        ]);

        $this->assertFalse($nextWeekResult);
    }

    // test generate all no existing students
    public function test_has_generate_all_run_this_week_returns_false_when_no_students_exist(): void
    {
        $result = $this->callPrivateMethod('hasGenerateAllRunThisWeek', [
            50,
            [],
        ]);

        $this->assertFalse($result);
    }

    // test generate all duplicate weekly db assignment
    public function test_has_generate_all_run_this_week_detects_duplicate_weekly_assignment_from_database(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $studentOneId = $this->createStudent($schoolId, $classroomId);
        $studentTwoId = $this->createStudent($schoolId, $classroomId);
        $studentThreeId = $this->createStudent($schoolId, $classroomId);
        $studentFourId = $this->createStudent($schoolId, $classroomId);
        $bookOneId = $this->createBook();
        $bookTwoId = $this->createBook();

        DB::table('book_student')->insert([
            [
                'book_id' => $bookOneId,
                'student_id' => $studentOneId,
                'school_id' => $schoolId,
                'status' => 'reading',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'book_id' => $bookTwoId,
                'student_id' => $studentTwoId,
                'school_id' => $schoolId,
                'status' => 'reading',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 4 students in class, ceil(4/2) = 2, 2 students have reading books created and controller should show generation as already used
        $result = $this->callPrivateMethod('hasGenerateAllRunThisWeek', [
            $classroomId,
            [$studentOneId, $studentTwoId, $studentThreeId, $studentFourId],
        ]);

        $this->assertTrue($result);
    }

    // test generate all ignore old assignments
    public function test_has_generate_all_run_this_week_ignores_old_assignments(): void
    {
        $schoolId = $this->createSchool();
        $classroomId = $this->createClassroom($schoolId);
        $studentOneId = $this->createStudent($schoolId, $classroomId);
        $studentTwoId = $this->createStudent($schoolId, $classroomId);
        $studentThreeId = $this->createStudent($schoolId, $classroomId);
        $studentFourId = $this->createStudent($schoolId, $classroomId);
        $bookOneId = $this->createBook();
        $bookTwoId = $this->createBook();

        DB::table('book_student')->insert([
            [
                'book_id' => $bookOneId,
                'student_id' => $studentOneId,
                'school_id' => $schoolId,
                'status' => 'reading',
                'created_at' => now()->subWeeks(2),
                'updated_at' => now()->subWeeks(2),
            ],
            [
                'book_id' => $bookTwoId,
                'student_id' => $studentTwoId,
                'school_id' => $schoolId,
                'status' => 'reading',
                'created_at' => now()->subWeeks(2),
                'updated_at' => now()->subWeeks(2),
            ],
        ]);

        $result = $this->callPrivateMethod('hasGenerateAllRunThisWeek', [
            $classroomId,
            [$studentOneId, $studentTwoId, $studentThreeId, $studentFourId],
        ]);

        $this->assertFalse($result);
    }

    // test generate all stock limit
    public function test_generate_all_checks_stock_limit_when_multiple_students_could_receive_same_book(): void
    {
        $schoolId = $this->createSchool();
        $teacherId = $this->createUser($schoolId, 'teacher');
        $classroomId = $this->createClassroom($schoolId, $teacherId);

        // both students lvl 2, getoxfordcolour returns red, both students will search for red books
        $studentOneId = $this->createStudent($schoolId, $classroomId, level: 2);
        $studentTwoId = $this->createStudent($schoolId, $classroomId, level: 2);

        // only one suitable red book exists w/ stock 1 - generateall should assign it to 1 student
        $bookId = $this->createBook(
            ortLevel: 2,
            ortColour: 'Red',
            title: 'Single Stock Red Book'
        );

        $this->createBookStock($schoolId, $bookId, stock: 1);
        $classroom = Classroom::findOrFail($classroomId);
        $response = $this->controller->generateAll(new Request(), $classroom);

        $this->assertEquals(
            'Books have been assigned to all students!',
            session('success')
        );

        $assignmentsForBook = DB::table('book_student')
            ->where('book_id', $bookId)
            ->where('status', 'reading')
            ->count();

        $this->assertEquals(
            1,
            $assignmentsForBook,
            'The one stock book should only be assigned to one student.'
        );

        $studentsWithReadingBook = DB::table('book_student')
            ->whereIn('student_id', [$studentOneId, $studentTwoId])
            ->where('status', 'reading')
            ->distinct('student_id')
            ->count('student_id');

        $this->assertEquals(
            1,
            $studentsWithReadingBook,
            'Only one of the two students should receive the only available book.'
        );
    }

    // test generate all weekly lock after generation
    public function test_generate_all_marks_weekly_lock_after_successful_generation(): void
    {
        $schoolId = $this->createSchool();
        $teacherId = $this->createUser($schoolId, 'teacher');
        $classroomId = $this->createClassroom($schoolId, $teacherId);

        $this->createStudent($schoolId, $classroomId, level: 2);

        $bookId = $this->createBook(
            ortLevel: 2,
            ortColour: 'Red',
            title: 'Available Red Book'
        );

        $this->createBookStock($schoolId, $bookId, stock: 5);
        $classroom = Classroom::findOrFail($classroomId);
        $this->controller->generateAll(new Request(), $classroom);
        $key = $this->callPrivateMethod('generateAllCacheKey', [$classroomId]);
        $this->assertTrue(Cache::has($key));
    }

    // test generate all no duplicate generation
    public function test_generate_all_blocks_duplicate_generation_in_same_week(): void
    {
        $schoolId = $this->createSchool();
        $teacherId = $this->createUser($schoolId, 'teacher');
        $classroomId = $this->createClassroom($schoolId, $teacherId);

        $this->createStudent($schoolId, $classroomId, level: 2);

        $bookId = $this->createBook(
            ortLevel: 2,
            ortColour: 'Red',
            title: 'Available Red Book'
        );

        $this->createBookStock($schoolId, $bookId, stock: 5);
        $classroom = Classroom::findOrFail($classroomId);
        $this->controller->generateAll(new Request(), $classroom);
        $this->controller->generateAll(new Request(), $classroom);
        $this->assertEquals(
            'Books have already been assigned to this class this week. Use manual assignment for individual students who need more books.',
            session('error')
        );
    }
}