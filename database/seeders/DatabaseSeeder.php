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

        $classConfig = [
            ['year' => 2, 'stage' => 'KS1'],
            ['year' => 3, 'stage' => 'KS2'],
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

        $randomHeadteacher = User::factory()->create([
            'username'  => 'randomheadteacher',
            'name'      => 'John Doe',
            'email'     => 'johnedoe@' . strtolower(str_replace(' ', '', $randomSchool->name)) . '.com',
            'phone'     => '07123456789',
            'role'      => 'headteacher',
            'school_id' => $randomSchool->id,
            'isAdmin'   => false,
            'pfp'       => '/images/pfp/cat.png',
        ]);

        // montgomery school
        $school = School::where('urn', '138864')->first();

        // Montgomery -- HEADTEACHER
        $headteacher = User::factory()->create([
            'username' => 'montgomeryheadteacher',
            'name' => 'Montgomery Headteacher',
            'email' => 'headteacher@montgomery.com',
            'phone' => '07123456789',
            'role' => 'headteacher',
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

            return Classroom::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'teacher_id' => $teacher->id,
                    'year_group' => $year,
                ],
                [
                    'name' => $baseName,
                    'stage' => $stage,
                    'academic_year' => '2026/2027',
                    'academic_start' => '2026',
                    'academic_end' => '2027',
                    'active' => true,
                ]
            );
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
                    'stock'      => rand(0, 2),
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

        foreach ($classrooms as $classroom) {

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

                // create students (assign same names to student profiles)
                $student = $user->student()->create([
                    'school_id' => $school->id,
                    'classroom_id' => $classroom->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'level' => fake()->numberBetween(7, 14), // level 7 = year 2, lvl 14 = year 3 at max
                    'date_of_birth' => now()->subYears(5 + $classroom->year_group)->subDays(rand(0, 365)),
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

                // ASSIGN READING LIST
                if ($validBookIds->isNotEmpty()) {
                    $readingListCount = rand(1, min(5, $validBookIds->count()));
                    // get random books from filter
                    $randomReadingListBooks = $validBookIds->random($readingListCount);
                    
                    $readingListPayload = [];
                    foreach ($randomReadingListBooks as $bookId) {
                        $readingListPayload[] = [
                            'school_id'    => $school->id,
                            'classroom_id' => $classroom->id,
                            'student_id'   => $student->id,
                            'book_id'      => $bookId,
                            'status'       => fake()->randomElement(['pending', 'reading', 'completed']),
                            'created_at'   => now(),
                            'updated_at'   => now(),
                        ];
                    }
                    DB::table('student_reading_lists')->insert($readingListPayload);
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
                DB::table('student_streaks')->insert([
                    'school_id' => $school->id,
                    'classroom_id' => $classroom->id,
                    'student_id' => $student->id,
                    'streak_count' => rand(0, 7),
                    'last_read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $students->push($student);
            }

            // assign students to pivot table and give them a school id too
            $classroom->students()->syncWithPivotValues(
                $students->pluck('id')->toArray(), 
                ['school_id' => $school->id]
            );
        }
        
        $this->command->info('Created test user and synced books and classrooms');

        $this->call([
            SyntheticSeeder::class,
            BookReviewSeeder::class,
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
}