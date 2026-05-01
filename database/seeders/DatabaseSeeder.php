<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use Illuminate\Support\Facades\Artisan;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Genre;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use App\Models\StudentWeeklyGoal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // ===================================================================
    // Kitten username words
    // ===================================================================
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

    public function run(): void
    {
        // User::factory(10)->create();
        // get a random school to assign to test user
        
        // KS1 - reception to year 2 (animals)
        // KS2 - year 3 to year 6 (authors)

        // ===================================================================
        // KS1 and KS2 names
        // ===================================================================
        $KS1Names = [
            'Ladybird', 'Bumblebee', 'Caterpillar', 'Butterfly', 'Dragonfly', 'Grasshopper', 'Snail', 'Frog', 'Toad',
            'Turtle', 'Rabbit', 'Hedgehog', 'Squirrel', 'Mouse', 'Deer', 'Otter', 'Panda', 'Koala', 'Kangaroo', 'Elephant',
            'Giraffe', 'Zebra', 'Lion'
        ];

        $KS2Names = [
            'Shakespeare', 'Murakami', 'Wilde', 'Dostoevsky', 'Dickens', 'Austen', 'Tolkien', 'Rowling', 'Carroll', 'Woolf', 'Bronte', 'Huxley',
            'Orwell', 'Salinger', 'Twain', 'Hemingway', 'Fitzgerald', 'Shelley', 'Verne', 'Wells', 'Bradbury',
            'Cather', 'Faulkner', 'Steinbeck', 'Poe', 'Doyle'
        ];

        // progressed class w/ normal class
        $classConfig = [
        [
            'year'           => 2,
            'stage'          => 'KS1',
            'academic_start' => 2024,
            'academic_end'   => 2025,
            'active'         => false,
            'is_progressed'  => true,
            'role'           => 'archived',
        ],
        [
            'year'           => 3,
            'stage'          => 'KS2',
            'academic_start' => 2025,
            'academic_end'   => 2026,
            'active'         => true,
            'is_progressed'  => false,
            'role'           => 'progressed_target',
        ],
        [
            'year'           => 3,
            'stage'          => 'KS2',
            'academic_start' => 2025,
            'academic_end'   => 2026,
            'active'         => true,
            'is_progressed'  => false,
            'role'           => 'standalone',
        ],
    ];

        $this->command->info('Importing schools');
        Artisan::call('schools:import');
        $this->command->info('Schools imported');

        $school = School::inRandomOrder()->first();

        // create an admin school
        $adminSchool = School::firstOrCreate(
            ['urn' => '000000'],
            [
                'name' => 'Admin Academy',
                'town' => 'Birmingham',
                'postcode' => 'B1 234',
            ]
        );

        // Testuser -- ADMIN
        User::factory()->create([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '07123456789',
            'role' => 'admin',
            'school_id' => $adminSchool->id,
            'isAdmin' => true,
            'pfp' => '/images/pfp/cat.png',
        ]);

        $randomSchool = School::where('id', '!=', $school->id)->inRandomOrder()->first();

        $randomSchoolAdmin = User::factory()->create([
            'username'  => 'randomSchoolAdmin',
            'name'      => 'John Doe',
            'email'     => 'johnedoe@' . strtolower(str_replace(' ', '', $randomSchool->name)) . '.com',
            'phone'     => '07123456789',
            'role'      => 'schooladmin',
            'school_id' => $randomSchool->id,
            'isAdmin'   => false,
            'pfp'       => '/images/pfp/cat.png',
        ]);

        // montgomery school
        $school = School::where('urn', '138864')->first();

        // Montgomery -- schooladmin
        $schooladmin = User::factory()->create([
            'username' => 'montgomeryschooladmin',
            'name' => 'Montgomery Admin',
            'email' => 'schooladmin@montgomery.com',
            'phone' => '07123456789',
            'role' => 'schooladmin',
            'school_id' => $school->id,
            'isAdmin' => false,
            'pfp' => '/images/pfp/lamb.png',
        ]);

        // Montgomery -- TEACHER
        $teacher = User::factory()->create([
            'username' => 'montgomeryteacher',
            'name' => 'Montgomery Teacher',
            'email' => 'teacher@montgomery.com',
            'phone' => '07123456789',
            'role' => 'teacher',
            'school_id' => $school->id,
            'isAdmin' => false,
            'pfp' => '/images/pfp/owl.png',
        ]);

        // Classrooms
        $classrooms = collect($classConfig)->map(function ($config) use ($school, $teacher, $KS1Names, $KS2Names) {
            $year = $config['year'];
            $stage = $config['stage'];

            $baseName = $stage === 'KS1' ? $KS1Names[array_rand($KS1Names)] : $KS2Names[array_rand($KS2Names)];

            // academic start and end
            $academicStart = $config['academic_start'];
            $academicEnd   = $config['academic_end'];

            // unique classrooms match w/ firstorcreate so it gets prev classes and not new ones
            $classroom = Classroom::firstOrCreate(
                [
                    'school_id'      => $school->id,
                    'teacher_id'     => $teacher->id,
                    'year_group'     => $year,
                    'academic_year'  => $academicStart . '/' . $academicEnd,
                    'name'           => $baseName,
                ],
                [
                    'stage'          => $stage,
                    'academic_start' => (string) $academicStart,
                    'academic_end'   => (string) $academicEnd,
                    'active'         => $config['active'],
                    'is_progressed'  => $config['is_progressed'],
                ]
            );

            // attach role for identification
            $classroom->setAttribute('seed_role', $config['role']);

            return $classroom;
        });

        // SYNC BOOKS AND GENRES
        $this->command->info('Syncing books and genres');
        $this->call([
            BookBackupSeeder::class,
        ]);
        $this->command->info('Books and genres synced');

        // ADD STOCK TO BOOKS
        $this->command->info('Adding stock to books');
        
        // chunk books by id
        DB::table('books')->orderBy('id')->chunk(1000, function ($books) use ($school) {
            $payload = [];
            $now = now()->toDateTimeString(); // get timestamp once per chunk
            
            // 1000 books at a time
            foreach ($books as $book) {
                $payload[] = [
                    'book_id'    => $book->id,
                    'school_id'  => $school->id,
                    'stock'      => rand(1, 2),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // add to table
            DB::table('book_school_stocks')->upsert(
                $payload, 
                ['book_id', 'school_id'],
                ['stock', 'updated_at']
            );
        });

        // ADD STOCK TO BOOKS FOR ADMIN SCHOOL
        DB::table('books')->orderBy('id')->chunk(1000, function ($books) use ($adminSchool) {
            $payload = [];
            $now = now()->toDateTimeString();
            
            foreach ($books as $book) {
                $payload[] = [
                    'book_id'    => $book->id,
                    'school_id'  => $adminSchool->id,
                    'stock'      => rand(1, 5),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('book_school_stocks')->upsert(
                $payload,
                ['book_id', 'school_id'],
                ['stock', 'updated_at']
            );
        });
        
        $this->command->info('Stock added to books');

        // Fetch all genre ids from db
        $allGenreIds = Genre::pluck('id');
        // all book ids
        $allBookIds = DB::table('books')->pluck('id');

        // Make sure pfp directory exists
        Storage::disk('public')->makeDirectory('pfp/kittens');

        // Create students and assign to classrooms
        $this->command->info('Creating students and downloading pfps');

        // collect archived students to move into progressed class
        $archivedClassroomStudents = collect();

        foreach ($classrooms as $classroom) {
            $role = $classroom->getAttribute('seed_role');

            // progressed target class (reused students from archived class)
            if ($role === 'progressed_target') {

                // move the archived students into new classroom
                foreach ($archivedClassroomStudents as $student) {
                    $student->update(['classroom_id' => $classroom->id]);
                }

                // pivot rows for the new ACTIVE classroom
                $classroom->students()->syncWithPivotValues(
                    $archivedClassroomStudents->pluck('id')->toArray(),
                    ['school_id' => $school->id, 'active' => 1, 'starts_on' => Carbon::create(2025, 9, 1)]
                );

                // give them MORE books inside the new academic year so the dashboard has data
                $this->seedBooksForExistingStudents(
                    $archivedClassroomStudents,
                    $classroom,
                    $school,
                    Carbon::create(2025, 9, 1)->startOfDay(),
                    now()
                );
                continue;
            }

            $students = collect();

            // 20-30 students per class
            foreach (range(1, rand(20, 30)) as $_) {

                // generate names once
                $username = $this->createStudentUsername();
                $firstName = fake()->firstName();
                $lastName = fake()->lastName();

                // assign names to the user
                $pfpPath = $this->downloadKittenPfp($username);

                // create user
                $user = User::factory()->create([
                    'username' => $username,
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $username . '@example.com',
                    'password' => 'password',
                    'phone' => '07' . rand(100000000, 999999999),
                    'role' => 'student',
                    'school_id' => $school->id,
                    'pfp' => $pfpPath,
                ]);

                // year group used for dob calculcation // archived class students = year younger
                $yearGroupForAge = $role === 'archived' ? $classroom->year_group : $classroom->year_group;

                // create students (assign same names to student profiles)
                $student = $user->student()->create([
                    'school_id' => $school->id,
                    'classroom_id' => $classroom->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'level' => fake()->numberBetween(7, 14), // level 7 = year 2, lvl 14 = year 3 at max
                    'date_of_birth' => now()->subYears(5 + $yearGroupForAge)->subDays(rand(0, 365)),
                    'pfp' => $pfpPath,
                ]);

                // ASSIGN WEEKLY BOOK GOAL
                StudentWeeklyGoal::create([
                    'school_id' => $school->id,
                    'classroom_id' => $classroom->id,
                    'student_id' => $student->id,
                    'target' => rand(1, 3),
                ]);

                // ASSIGN LIKED GENRES
                if ($allGenreIds->isNotEmpty()) {
                    $randomGenres = $allGenreIds->random(rand(1, 3));
                    
                    $pivotData = [];
                    foreach ($randomGenres as $genreId) {
                        $pivotData[$genreId] = ['school_id' => $school->id];
                    }
                    
                    $student->preferredGenres()->attach($pivotData);
                }

                // ===================================================================
                // COLOURS
                // ===================================================================
                $colourBands = [
                    'Light Purple', 'Pink', 'Red', 'Yellow', 'Light Blue', 'Green', 'Orange', 
                    'Turquoise', 'Purple', 'Gold', 'White', 'Lime', 'Lime+', 'Grey', 'Dark Blue', 'Dark Red'
                ];

                $studentColour = match ((int)$student->level) {
                    0 => 'Light Purple', 1 => 'Pink', 2 => 'Red', 3 => 'Yellow', 4 => 'Light Blue',
                    5 => 'Green', 6 => 'Orange', 7 => 'Turquoise', 8 => 'Purple', 9 => 'Gold',
                    10 => 'White', 11 => 'Lime', 12 => 'Lime+', 13, 14 => 'Grey',
                    15, 16 => 'Dark Blue', 17, 18, 19, 20 => 'Dark Red',
                    default => 'Dark Red',
                };

                $currentIndex = array_search($studentColour, $colourBands);
                if ($currentIndex === false) $currentIndex = 1;

                $validColours = [];
                $validColours[] = $colourBands[$currentIndex]; // same level
                if ($currentIndex > 0) $validColours[] = $colourBands[$currentIndex - 1]; // level below
                if ($currentIndex < count($colourBands) - 1) $validColours[] = $colourBands[$currentIndex + 1]; // level above

                // filter available books by book colours within students range
                $validBookIds = DB::table('books')
                    ->whereIn('id', $allBookIds)
                    ->whereIn('ort_colour', $validColours)
                    ->pluck('id');

                // assign books that student has actually read w/ reviews FOR STATISTICS PAGE
                if ($validBookIds->isNotEmpty()) {
                    // find out the academic timeframe of this classroom so the dates makes sense
                    // archived class is special -> ends on 20th july 2025 (actual end ofg term date)
                    if ($role === 'archived') {
                        $awStart = Carbon::create(2024, 9, 1)->startOfDay();
                        $awEnd   = Carbon::create(2025, 7, 20)->endOfDay();
                    } else {
                        $awStart = Carbon::create((int) $classroom->academic_start, 9, 1)->startOfDay(); // week start
                        $awEnd   = Carbon::create((int) $classroom->academic_end, 8, 31)->endOfDay(); // week end
                        if ($awEnd->gt(now())) {
                            $awEnd = now(); // dont create stuff after the dates
                        }
                    }
                    $daysSpan = max(0, $awStart->diffInDays($awEnd));

                    // book review titles and descriptions
                    $reviewTitles = [
                        'Loved this book!', 'Great read for kids', 'My child enjoyed it', 'Fantastic story', 'Really engaging',
                        'Perfect for bedtime', 'Wonderful illustrations', 'Highly recommend', 'Good but short', 'A new favourite',
                        'Brilliant book', 'Fun and educational', 'Could not put it down', 'Nice story overall', 'Great for early readers',
                        'Entertaining read', 'Well written', 'Colourful and fun', 'A bit boring', 'Not bad at all',
                    ];
                    $reviewDescriptions = [
                        'My child absolutely loved reading this book. The story kept them engaged from start to finish.',
                        'A wonderful book for young readers. The language is accessible and the story is interesting throughout.',
                        'We read this together at bedtime and it was perfect. A lovely story my child really connected with.',
                        'The illustrations are beautiful and really bring the story to life.',
                        'Great for building confidence in reading. Vocabulary is age-appropriate.',
                        'A solid book that does what it needs to do. A reliable choice for reading practice at this level.',
                        'My child was not particularly interested in this one but the story is okay overall.',
                        'Excellent book for this reading level. Challenges without being too difficult.',
                        'A real favourite in our household that I would recommend to anyone.',
                        'Engaging and my child learned new words. Great addition to our collection.',
                    ];
                    $ratingPool     = [1, 2, 3, 3, 3, 4, 4, 4, 4, 5, 5, 5, 5, 5]; // ratings
                    $difficultyPool = ['easy', 'easy', 'easy', 'okay', 'okay', 'okay', 'okay', 'okay', 'hard', 'hard']; // difficulties

                    // each student reads between 4/12 books the year
                    $bookStudentCount = rand(4, min(12, $validBookIds->count()));
                    $readBookIds = $validBookIds->random($bookStudentCount);
                    if (!($readBookIds instanceof \Illuminate\Support\Collection)) {
                        $readBookIds = collect([$readBookIds]);
                    }

                    $bookStudentRows = [];
                    $reviewRows = [];

                    // for each book
                    foreach ($readBookIds as $bookId) {
                        // 3/4 completed 1/4 reading for chart data
                        $status = fake()->randomElement(['completed', 'completed', 'completed', 'reading']);
                        $readDate = $awStart->copy()->addDays(rand(0, $daysSpan))->format('Y-m-d H:i:s');

                        // book_student
                        $bookStudentRows[] = [
                            'school_id'  => $school->id,
                            'student_id' => $student->id,
                            'book_id'    => $bookId,
                            'status'     => $status,
                            'created_at' => $readDate,
                            'updated_at' => $readDate,
                        ];

                        // 75% of the time the student writes a review with matching dates
                        if (rand(1, 100) <= 75) {
                            // reviews
                            $reviewRows[] = [
                                'school_id'   => $school->id,
                                'student_id'  => $student->id,
                                'book_id'     => $bookId,
                                'rating'      => $ratingPool[array_rand($ratingPool)],
                                'difficulty'  => $difficultyPool[array_rand($difficultyPool)],
                                'title'       => $reviewTitles[array_rand($reviewTitles)],
                                'description' => $reviewDescriptions[array_rand($reviewDescriptions)],
                                'upvotes'     => rand(0, 25),
                                'created_at'  => $readDate,
                                'updated_at'  => $readDate,
                            ];
                        }
                    }

                    if (!empty($bookStudentRows)) {
                        DB::table('book_student')->insert($bookStudentRows);
                    }
                    if (!empty($reviewRows)) {
                        DB::table('book_reviews')->insert($reviewRows);
                    }
                }

                // ASSIGN FAVOURITE BOOKS
                if ($validBookIds->isNotEmpty()) {
                    $favesCount = rand(1, min(10, $validBookIds->count()));
                    // get random books from filter
                    $randomFavesBooks = $validBookIds->random($favesCount);
                    
                    $favesPayload = [];
                    foreach ($randomFavesBooks as $bookId) {
                        $favesPayload[] = [
                            'school_id'    => $school->id,
                            'classroom_id' => $classroom->id,
                            'student_id'   => $student->id,
                            'book_id'      => $bookId,
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                    DB::table('student_favourite_books')->insert($favesPayload);
                }

                // STUDENT STREAKS
                DB::table('student_streaks')->updateOrInsert(
                    // one row each
                    ['student_id' => $student->id],
                    [
                        'school_id'    => $school->id,
                        'classroom_id' => $classroom->id,
                        'streak_count' => rand(0, 7),
                        'last_read_at' => null,
                        'updated_at'   => now(),
                    ]
                );
                $students->push($student);
            }

            // assign students to pivot table w/ school id, archived class -> pivot (active = false), ends_on set to day they were archived
            if ($role === 'archived') {
                $classroom->students()->syncWithPivotValues(
                    $students->pluck('id')->toArray(),
                    [
                        'school_id' => $school->id,
                        'active'    => 0,
                        'starts_on' => Carbon::create(2024, 9, 1),
                        'ends_on'   => Carbon::create(2025, 7, 20),
                    ]
                );

                // insert archive_classrooms so restoreclass/showstats can find it
                DB::table('archive_classrooms')->insert([
                    'school_id'     => $school->id,
                    'classroom_id'  => $classroom->id,
                    'student_ids'   => json_encode($students->pluck('id')->toArray()),
                    'academic_year' => $classroom->academic_year,
                    'year_group'    => $classroom->year_group,
                    'stage'         => $classroom->stage,
                    'created_at'    => Carbon::create(2025, 7, 20),
                    'updated_at'    => Carbon::create(2025, 7, 20),
                ]);

                // hold students so progressed class can reuse them
                $archivedClassroomStudents = $students;

            } else {
                $classroom->students()->syncWithPivotValues(
                    $students->pluck('id')->toArray(),
                    ['school_id' => $school->id, 'active' => 1]
                );
            }
        }
        
        $this->command->info('Created test user and synced books and classrooms');

        $this->call([
            SyntheticSeeder::class,
        ]);
    }

    // Create student usernames
    protected function createStudentUsername(): string
    {
        $adjective = $this->kittenAdjectives[array_rand($this->kittenAdjectives)];
        $name      = $this->kittenNames[array_rand($this->kittenNames)];
        $number    = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        return $adjective . $name . $number;
    }

    // ===================================================================
    // Download and store PFP from Robohash
    // stored at storage/app/public/pfp
    // ===================================================================
    protected function downloadKittenPfp(string $username): string
    {
        // set path w/ image
        $storagePath = 'pfp/kittens/' . $username . '.png';

        // Reuse if already downloaded (locally)
        if (Storage::disk('public')->exists($storagePath)) {
            return Storage::url($storagePath);
        }

        // set=set4  → kittens
        // bgset=bg1 → coloured background
        // size      → 200x200
        $robohashUrl = sprintf(
            'https://robohash.org/%s?set=set4&bgset=bg1&size=200x200',
            urlencode($username)
        );

        // Skip downloading pfps since theyll load from robohash
        if (app()->environment('local')) {
            return $robohashUrl; 
        }

        try {
            $response = Http::timeout(3)->get($robohashUrl);

            if ($response->successful()) {
                Storage::disk('public')->put($storagePath, $response->body());
                return Storage::url($storagePath);
            } else {
                $this->command->warn(" Failed to download pfp for {$username} (HTTP {$response->status()})");
                return '/images/pfp/cat.png';
            }

        } catch (ConnectionException $e) {
            $this->command->warn("  Timeout for {$username} - Using a default image");
            return '/images/pfp/cat.png';
        } catch (\Exception $e) {
            $this->command->warn("  Exception for {$username}: " . $e->getMessage());
            // just set to cat if it doesnt work
            return '/images/pfp/cat.png';
        }
    }

    // seed books for existing students and book_reviews rows for already existing students // used when same students get progressed
    protected function seedBooksForExistingStudents($students, $classroom, $school, Carbon $awStart, Carbon $awEnd): void
    {
        // seed books for existing students
        $allBookIds = DB::table('books')->pluck('id');
        $colourBands = [
            'Light Purple', 'Pink', 'Red', 'Yellow', 'Light Blue', 'Green', 'Orange',
            'Turquoise', 'Purple', 'Gold', 'White', 'Lime', 'Lime+', 'Grey', 'Dark Blue', 'Dark Red'
        ];
        $reviewTitles = [
            'Loved this book!', 'Great read for kids', 'Fantastic story', 'Really engaging',
            'Highly recommend', 'A new favourite', 'Brilliant book', 'Could not put it down',
            'Great for early readers', 'Entertaining read', 'Well written',
        ];
        $reviewDescriptions = [
            'Engaging and my child learned new words. Great addition to our collection.',
            'A real favourite in our household that I would recommend to anyone.',
            'Excellent book for this reading level. Challenges without being too difficult.',
            'A solid book that does what it needs to do.',
            'Great for building confidence in reading.',
        ];
        $ratingPool     = [3, 3, 4, 4, 4, 5, 5, 5, 5];
        $difficultyPool = ['easy', 'okay', 'okay', 'okay', 'hard'];

        // academic week end
        if ($awEnd->gt(now())) {
            $awEnd = now();
        }
        $daysSpan = max(0, $awStart->diffInDays($awEnd));

        // loop through students
        foreach ($students as $student) {
            // which colour books are valid 
            $studentColour = match ((int) $student->level) {
                0 => 'Light Purple', 1 => 'Pink', 2 => 'Red', 3 => 'Yellow', 4 => 'Light Blue',
                5 => 'Green', 6 => 'Orange', 7 => 'Turquoise', 8 => 'Purple', 9 => 'Gold',
                10 => 'White', 11 => 'Lime', 12 => 'Lime+', 13, 14 => 'Grey',
                15, 16 => 'Dark Blue', 17, 18, 19, 20 => 'Dark Red',
                default => 'Dark Red',
            };

            $idx = array_search($studentColour, $colourBands);
            if ($idx === false) $idx = 1;

            $validColours = [$colourBands[$idx]];
            if ($idx > 0) $validColours[] = $colourBands[$idx - 1];
            if ($idx < count($colourBands) - 1) $validColours[] = $colourBands[$idx + 1];

            $validBookIds = DB::table('books')
                ->whereIn('id', $allBookIds)
                ->whereIn('ort_colour', $validColours)
                ->pluck('id');

            if ($validBookIds->isEmpty()) continue;

            $count = rand(4, min(12, $validBookIds->count()));
            $readBookIds = $validBookIds->random($count);
            if (!($readBookIds instanceof \Illuminate\Support\Collection)) {
                $readBookIds = collect([$readBookIds]);
            }

            $bookStudentRows = [];
            $reviewRows = [];

            foreach ($readBookIds as $bookId) {
                $status = fake()->randomElement(['completed', 'completed', 'completed', 'reading']);
                $readDate = $awStart->copy()->addDays(rand(0, $daysSpan))->format('Y-m-d H:i:s');

                $bookStudentRows[] = [
                    'school_id'  => $school->id,
                    'student_id' => $student->id,
                    'book_id'    => $bookId,
                    'status'     => $status,
                    'created_at' => $readDate,
                    'updated_at' => $readDate,
                ];

                // 75% chance to insert
                if (rand(1, 100) <= 75) {
                    $reviewRows[] = [
                        'school_id'   => $school->id,
                        'student_id'  => $student->id,
                        'book_id'     => $bookId,
                        'rating'      => $ratingPool[array_rand($ratingPool)],
                        'difficulty'  => $difficultyPool[array_rand($difficultyPool)],
                        'title'       => $reviewTitles[array_rand($reviewTitles)],
                        'description' => $reviewDescriptions[array_rand($reviewDescriptions)],
                        'upvotes'     => rand(0, 25),
                        'created_at'  => $readDate,
                        'updated_at'  => $readDate,
                    ];
                }
            }

            if (!empty($bookStudentRows)) DB::table('book_student')->insert($bookStudentRows);
            if (!empty($reviewRows))      DB::table('book_reviews')->insert($reviewRows);

            // weekly goal and streak for the new classroom
            // move existing weekly goal to new classroom
            $existingGoal = StudentWeeklyGoal::where('student_id', $student->id)->first();

            if ($existingGoal) {
                $existingGoal->update([
                    'classroom_id' => $classroom->id,
                ]);
            } else {
                // if none, create one
                StudentWeeklyGoal::create([
                    'school_id'    => $school->id,
                    'classroom_id' => $classroom->id,
                    'student_id'   => $student->id,
                    'target'       => rand(1, 3),
                ]);
            }
            // move students streak to new classroom
            DB::table('student_streaks')
                ->where('student_id', $student->id)
                ->update([
                    'classroom_id' => $classroom->id,
                    'updated_at'   => now(),
                ]);
        }
    }
}