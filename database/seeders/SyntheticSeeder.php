<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\School;
use App\Models\Genre;
use Carbon\Carbon;

class SyntheticSeeder extends Seeder
{
    protected array $kittenAdjectives = [
        'Fluffy', 'Tiny', 'Soft', 'Fuzzy', 'Cuddly', 'Silky', 'Velvet', 'Plump', 'Sleek', 'Striped',
        'Spotted', 'Patchy', 'Ginger', 'Snowy', 'Ebony', 'Creamy', 'Dusty', 'Misty', 'Smoky', 'Tabby',
        'Cute', 'Sweet', 'Playful', 'Sleepy', 'Purring', 'Lazy', 'Bouncy', 'Curious', 'Sneaky', 'Grumpy',
        'Cheeky', 'Jolly', 'Witty', 'Brave', 'Shy', 'Dainty', 'Sassy', 'Dizzy', 'Clumsy', 'Peppy',
        'Magic', 'Cosmic', 'Starry', 'Dreamy', 'Lucky', 'Sparkly', 'Rainbow', 'Bubbly', 'Zesty', 'Glittery',
        'Funky', 'Jazzy', 'Snazzy', 'Fancy', 'Royal', 'Mighty', 'Speedy', 'Nifty', 'Cozy', 'Hyper',
        'Wobbly', 'Scruffy', 'Shaggy', 'Perky', 'Jumpy', 'Loopy', 'Zippy', 'Dozy', 'Nosy', 'Quirky',
    ];

    protected array $kittenNames = [
        'Kitten', 'Kitty', 'Cat', 'Paws', 'Whiskers', 'Mittens', 'Furball', 'Tabby', 'Moggy', 'Tomcat',
        'Mochi', 'Biscuit', 'Waffle', 'Pancake', 'Muffin', 'Cookie', 'Brownie', 'Pudding', 'Custard', 'Toffee',
        'Butterscotch', 'Caramel', 'Cheddar', 'Pretzel', 'Nugget', 'Noodle', 'Dumpling', 'Pickle', 'Peanut', 'Cocoa',
        'Petal', 'Blossom', 'Meadow', 'Clover', 'Acorn', 'Hazel', 'Willow', 'Daisy', 'Fern', 'Briar',
        'Snuggle', 'Cuddle', 'Bubble', 'Doodle', 'Sprinkle', 'Twinkle', 'Dimple', 'Freckle', 'Marble', 'Pebble',
        'Meow', 'Purr', 'Mrow', 'Nap', 'Zoomie', 'Floof', 'Boop', 'Bonk', 'Chirp', 'Trill',
    ];

    protected array $ks1Names = [
        'Ladybird', 'Bumblebee', 'Caterpillar', 'Butterfly', 'Dragonfly', 'Grasshopper', 'Snail', 'Frog', 'Toad',
        'Turtle', 'Rabbit', 'Hedgehog', 'Squirrel', 'Mouse', 'Deer', 'Otter', 'Panda', 'Koala', 'Kangaroo', 'Elephant',
        'Giraffe', 'Zebra', 'Lion'
    ];

    protected array $ks2Names = [
        'Shakespeare', 'Murakami', 'Wilde', 'Dostoevsky', 'Dickens', 'Austen', 'Tolkien', 'Rowling', 'Carroll', 'Woolf',
        'Bronte', 'Huxley', 'Orwell', 'Salinger', 'Twain', 'Hemingway', 'Fitzgerald', 'Shelley', 'Verne', 'Wells',
        'Bradbury', 'Cather', 'Faulkner', 'Steinbeck', 'Poe', 'Doyle'
    ];

    protected array $studentPfps = [
        '/images/pfp/cat.png',
        '/images/pfp/owl.png',
        '/images/pfp/lamb.png',
        '/images/pfp/cat.png',
        '/images/pfp/owl.png',
        '/images/pfp/lamb.png',
    ];

    protected array $teacherPfps = [
        '/images/pfp/owl.png',
        '/images/pfp/lamb.png',
        '/images/pfp/cat.png',
    ];

    protected array $colourBands = [
        'Light Purple', 'Pink', 'Red', 'Yellow', 'Light Blue', 'Green', 'Orange',
        'Turquoise', 'Purple', 'Gold', 'White', 'Lime', 'Lime+', 'Grey', 'Dark Blue', 'Dark Red'
    ];

    // book review titles and descriptions
    protected array $reviewTitles = [
        'Loved this book!', 'Great read for kids', 'My child enjoyed it', 'Fantastic story', 'Really engaging',
        'Perfect for bedtime', 'Wonderful illustrations', 'Highly recommend', 'Good but short', 'A new favourite',
        'Brilliant book', 'Fun and educational', 'Could not put it down', 'Nice story overall', 'Great for early readers',
        'Entertaining read', 'Well written', 'Colourful and fun', 'A bit boring', 'Not bad at all',
        'My kids ask for this every night', 'A lovely little book', 'Good for phonics practice', 'Simple but enjoyable',
        'One of the best', 'Average book', 'Pretty decent', 'Exceeded expectations', 'Would read again', 'Great value',
    ];

    protected array $reviewDescriptions = [
        'My child absolutely loved reading this book. The story kept them engaged from start to finish and they asked to read it again straight away.',
        'A wonderful book for young readers. The language is accessible and the story is interesting enough to hold their attention throughout.',
        'We read this together at bedtime and it was perfect. Not too long, not too short, with a lovely story that my child really connected with.',
        'The illustrations are beautiful and really bring the story to life. My child spent ages looking at every page and pointing things out.',
        'Great for building confidence in reading. The vocabulary is age-appropriate and the sentences are well structured for early readers.',
        'A solid book that does what it needs to do. Nothing extraordinary but a reliable choice for reading practice at this level.',
        'My child was not particularly interested in this one. It took a few attempts to get through it but the story is okay overall.',
        'Excellent book for this reading level. It challenges without being too difficult and the story is genuinely entertaining for children.',
        'We have read this one many times now and it never gets old. A real favourite in our household that I would recommend to anyone.',
        'The story is engaging and my child learned some new words from it. A great addition to our reading collection at home.',
        'Perfect length for a quick reading session. The plot is simple but effective and my child understood it without any help.',
        'I was surprised by how much my child enjoyed this. They usually prefer different types of books but this one really captured their imagination.',
        'A good book for practising phonics sounds. The repetition is helpful without being tedious and the story ties everything together nicely.',
        'Not our favourite but still a decent read. The story could be more exciting but the language and structure are good for this level.',
        'My child brought this home from school and we both enjoyed it. Well written with a clear message that children can easily understand.',
    ];

    // rating and difficulty weights
    protected array $ratingWeights = [1, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 5];
    protected array $difficultyWeights = [
        'easy', 'easy', 'easy',
        'okay', 'okay', 'okay', 'okay', 'okay',
        'hard', 'hard',
    ];

    public function run(): void
    {
        $this->command->info('Starting synthetic seeder');

        $now = now()->format('Y-m-d H:i:s');
        $hashedPassword = bcrypt('password');

        // academic year 
        [$academicStart, $academicEnd] = $this->currentAcademicYear();

        $books = DB::table('books')
            ->select('id', 'ort_colour')
            ->get();

        $booksByColour = $books->groupBy('ort_colour')->map(fn ($group) => $group->pluck('id')->values());
        $allBookIds = $books->pluck('id')->values();
        $allGenreIds = Genre::pluck('id')->values();

        $schools = School::query()
            ->where('urn', '!=', '000000')
            ->inRandomOrder()
            ->limit(rand(10, 20))
            ->get();

        if ($schools->isEmpty()) {
            $this->command->warn('No schools found.');
            return;
        }

        foreach ($schools as $school) {
            $this->seedSchool($school, $allGenreIds, $booksByColour, $allBookIds, $now, $hashedPassword, $academicStart, $academicEnd);
        }

        $this->command->info('Synthetic seeder complete');
    }

    // SEED SCHOOL
    protected function seedSchool($school, $allGenreIds, $booksByColour, $allBookIds, string $now, string $hashedPassword, int $academicStart, int $academicEnd): void
    {
        $teacherCount = rand(5, 10);

        for ($t = 0; $t < $teacherCount; $t++) {
            $teacherFirst = fake()->firstName();
            $teacherLast = fake()->lastName();
            $teacherUsername = strtolower($teacherFirst . $teacherLast . rand(100, 999));
            $teacherRole = fake()->randomElement(['teacher', 'headteacher']);

            $teacherId = DB::table('users')->insertGetId([
                'name' => $teacherFirst . ' ' . $teacherLast,
                'username' => $teacherUsername,
                'phone' => '07' . rand(100000000, 999999999),
                'email' => $teacherUsername . '@' . Str::slug($school->name, '') . '.com',
                'email_verified_at' => $now,
                'password' => $hashedPassword,
                'school_id' => $school->id,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
                'isAdmin' => 0,
                'role' => $teacherRole,
                'pfp' => $this->teacherPfps[array_rand($this->teacherPfps)],
            ]);

            $classroomCount = rand(1, 2);

            for ($c = 0; $c < $classroomCount; $c++) {
                $year = fake()->randomElement([2, 3, 4, 5, 6]);
                $stage = $year <= 2 ? 'KS1' : 'KS2';
                $classroomName = $stage === 'KS1'
                    ? $this->ks1Names[array_rand($this->ks1Names)]
                    : $this->ks2Names[array_rand($this->ks2Names)];

                $classroomId = DB::table('classrooms')->insertGetId([
                    'school_id' => $school->id,
                    'teacher_id' => $teacherId,
                    'year_group' => $year,
                    'name' => $classroomName . ' ' . ($c + 1),
                    'stage' => $stage,
                    'academic_year' => $academicStart . '/' . $academicEnd,
                    'academic_start' => (string) $academicStart,
                    'academic_end' => (string) $academicEnd,
                    'active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $this->seedClassroom($school->id, $classroomId, $year, $allGenreIds, $booksByColour, $allBookIds, $now, $hashedPassword, $academicStart, $academicEnd);
            }
        }
    }

    // SEED CLASSROOM
    protected function seedClassroom(int $schoolId, int $classroomId, int $year, $allGenreIds, $booksByColour, $allBookIds, string $now, string $hashedPassword, int $academicStart, int $academicEnd): void
    {
        $studentCount = rand(20, 30);

        $userRows = [];
        $studentMeta = [];

        for ($i = 0; $i < $studentCount; $i++) {
            $username = $this->createUniqueUsername();
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $pfp = $this->studentPfps[array_rand($this->studentPfps)];

            // user rows
            $userRows[] = [
                'name' => $firstName . ' ' . $lastName,
                'username' => $username,
                'phone' => '07' . rand(100000000, 999999999),
                'email' => strtolower($username) . rand(100, 999) . '@example.com',
                'email_verified_at' => $now,
                'password' => $hashedPassword,
                'school_id' => $schoolId,
                'remember_token' => Str::random(10),
                'created_at' => $now,
                'updated_at' => $now,
                'isAdmin' => 0,
                'role' => 'student',
                'pfp' => $pfp,
            ];

            $studentMeta[] = [
                'username' => $username,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'level' => fake()->numberBetween(7, 18),
                'date_of_birth' => now()->subYears(5 + $year)->subDays(rand(0, 365))->toDateString(),
                'active' => 1,
                'is_exceptional' => fake()->boolean(8) ? 1 : 0,
                'pfp' => $pfp,
            ];
        }

        foreach (array_chunk($userRows, 500) as $chunk) {
            DB::table('users')->insert($chunk);
        }

        $insertedUsers = DB::table('users')
            ->where('school_id', $schoolId)
            ->whereIn('username', collect($studentMeta)->pluck('username'))
            ->select('id', 'username')
            ->get()
            ->keyBy('username');

        $studentRows = [];
        // student data
        foreach ($studentMeta as $meta) {
            $userId = $insertedUsers[$meta['username']]->id ?? null;
            if (!$userId) {
                continue;
            }

            $studentRows[] = [
                'school_id' => $schoolId,
                'user_id' => $userId,
                'classroom_id' => $classroomId,
                'first_name' => $meta['first_name'],
                'last_name' => $meta['last_name'],
                'level' => $meta['level'],
                'date_of_birth' => $meta['date_of_birth'],
                'active' => $meta['active'],
                'is_exceptional' => $meta['is_exceptional'],
                'pfp' => $meta['pfp'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // chunk 500 at a time
        foreach (array_chunk($studentRows, 500) as $chunk) {
            DB::table('students')->insert($chunk);
        }

        $userIds = $insertedUsers->pluck('id')->values();

        $students = DB::table('students')
            ->where('school_id', $schoolId)
            ->where('classroom_id', $classroomId)
            ->whereIn('user_id', $userIds)
            ->select('id', 'user_id', 'level')
            ->get();

        $pivotRows = [];
        $goalRows = [];
        $genreRows = [];
        $readingRows = [];
        $favouriteRows = [];
        $streakRows = [];
        $reviewRows = [];

        // loop through students
        foreach ($students as $student) {
            $pivotRows[] = [
                'school_id' => $schoolId,
                'classroom_id' => $classroomId,
                'student_id' => $student->id,
            ];

            // weekly goals
            $goalRows[] = [
                'school_id' => $schoolId,
                'classroom_id' => $classroomId,
                'student_id' => $student->id,
                'target' => rand(1, 3),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // genres
            if ($allGenreIds->isNotEmpty()) {
                $genreCount = rand(1, min(3, $allGenreIds->count()));
                $genreSelection = $allGenreIds->random($genreCount);
                if (!($genreSelection instanceof \Illuminate\Support\Collection)) {
                    $genreSelection = collect([$genreSelection]);
                }

                foreach ($genreSelection as $genreId) {
                    $genreRows[] = [
                        'genre_id' => $genreId,
                        'student_id' => $student->id,
                        'school_id' => $schoolId,
                    ];
                }
            }

            $validBookIds = $this->getValidBookIdsForLevel($student->level, $booksByColour, $allBookIds);

            // reading count
            $readingCount = rand(3, min(12, $validBookIds->count()));
            $readingSelection = $validBookIds->random($readingCount);
            if (!($readingSelection instanceof \Illuminate\Support\Collection)) {
                $readingSelection = collect([$readingSelection]);
            }

            // reading rows with 'real' dates
            foreach ($readingSelection as $bookId) {
                $status = fake()->randomElement(['completed', 'completed', 'completed', 'reading']); // bias towards completed
                $bookDate = $this->randomDateInAcademicYear($academicStart, $academicEnd);

                $readingRows[] = [
                    'school_id' => $schoolId,
                    'student_id' => $student->id,
                    'book_id' => $bookId,
                    'status' => $status,
                    'created_at' => $bookDate,
                    'updated_at' => $bookDate,
                ];

                // 75% of finished/reading books get a review with timestamps matching the read
                if (rand(1, 100) <= 75) {
                    $reviewRows[] = [
                        'school_id' => $schoolId,
                        'student_id' => $student->id,
                        'book_id' => $bookId,
                        'rating' => $this->ratingWeights[array_rand($this->ratingWeights)],
                        'difficulty' => $this->difficultyWeights[array_rand($this->difficultyWeights)],
                        'title' => $this->reviewTitles[array_rand($this->reviewTitles)],
                        'description' => $this->reviewDescriptions[array_rand($this->reviewDescriptions)],
                        'upvotes' => rand(0, 25),
                        'created_at' => $bookDate,
                        'updated_at' => $bookDate,
                    ];
                }
            }

            // fav count
            $favouriteCount = rand(1, min(8, $validBookIds->count()));
            $favouriteSelection = $validBookIds->random($favouriteCount);
            if (!($favouriteSelection instanceof \Illuminate\Support\Collection)) {
                $favouriteSelection = collect([$favouriteSelection]);
            }

            // favourite books
            foreach ($favouriteSelection as $bookId) {
                $favouriteRows[] = [
                    'school_id' => $schoolId,
                    'classroom_id' => $classroomId,
                    'student_id' => $student->id,
                    'book_id' => $bookId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // streaks
            $streakRows[] = [
                'school_id' => $schoolId,
                'classroom_id' => $classroomId,
                'student_id' => $student->id,
                'streak_count' => rand(0, 7),
                'last_read_at' => fake()->boolean(70) ? now()->subDays(rand(0, 14))->format('Y-m-d H:i:s') : null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // run these dbs
        $this->insertChunked('classroom_student', $pivotRows);
        $this->insertChunked('student_weekly_goals', $goalRows);
        $this->insertChunked('genre_student', $genreRows);
        $this->insertChunked('book_student', $readingRows);
        $this->insertChunked('student_favourite_books', $favouriteRows);
        $this->insertChunked('student_streaks', $streakRows);
        $this->insertChunked('book_reviews', $reviewRows);
    }

    // get valid book ids for the correct level
    protected function getValidBookIdsForLevel(int $level, $booksByColour, $allBookIds)
    {
        $studentColour = match ((int)$level) {
            0 => 'Light Purple', 1 => 'Pink', 2 => 'Red', 3 => 'Yellow', 4 => 'Light Blue',
            5 => 'Green', 6 => 'Orange', 7 => 'Turquoise', 8 => 'Purple', 9 => 'Gold',
            10 => 'White', 11 => 'Lime', 12 => 'Lime+', 13, 14 => 'Grey',
            15, 16 => 'Dark Blue', 17, 18, 19, 20 => 'Dark Red',
            default => 'Dark Red',
        };

        $currentIndex = array_search($studentColour, $this->colourBands);
        if ($currentIndex === false) {
            $currentIndex = 1;
        }

        $validColours = [$this->colourBands[$currentIndex]];
        if ($currentIndex > 0) {
            $validColours[] = $this->colourBands[$currentIndex - 1];
        }
        if ($currentIndex < count($this->colourBands) - 1) {
            $validColours[] = $this->colourBands[$currentIndex + 1];
        }

        $validBookIds = collect();
        foreach ($validColours as $colour) {
            if (isset($booksByColour[$colour])) {
                $validBookIds = $validBookIds->merge($booksByColour[$colour]);
            }
        }

        return $validBookIds->isNotEmpty() ? $validBookIds->values() : $allBookIds;
    }

    // chunked data
    protected function insertChunked(string $table, array $rows, int $size = 500): void
    {
        if (empty($rows)) {
            return;
        }

        foreach (array_chunk($rows, $size) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }

    // unique username
    protected function createUniqueUsername(): string
    {
        return $this->kittenAdjectives[array_rand($this->kittenAdjectives)]
            . $this->kittenNames[array_rand($this->kittenNames)]
            . str_pad((string) rand(1, 999), 3, '0', STR_PAD_LEFT)
            . rand(10, 99);
    }

    // current academic year, sep (curr year) to aug (next year)
    protected function currentAcademicYear(): array
    {
        $month = (int) now()->format('m');
        $year  = (int) now()->format('Y');
        $start = $month >= 9 ? $year : $year - 1;
        return [$start, $start + 1];
    }

    // random date in year
    protected function randomDateInAcademicYear(int $academicStart, int $academicEnd): string
    {
        $start = Carbon::create($academicStart, 9, 1)->startOfDay();
        $end   = Carbon::create($academicEnd, 8, 31)->endOfDay();

        $now = Carbon::now();
        if ($end->gt($now)) {
            $end = $now;
        }
        if ($start->gt($end)) {
            $start = $now->copy()->subMonths(11);
        }

        $daysDiff = $start->diffInDays($end);
        return $start->copy()->addDays(rand(0, max(0, $daysDiff)))->format('Y-m-d H:i:s');
    }
}