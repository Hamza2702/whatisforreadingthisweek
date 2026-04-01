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

        // Testuser -- ADMIN
        User::factory()->create([
            'username' => 'testuser',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '07123456789',
            'role' => 'admin',
            'school_id' => $school?->id,
            'isAdmin' => true,
            'pfp' => '/images/pfp/cat.png',
        ]);

        // Montgomery school
        $school = School::firstOrCreate([
            'urn' => '138864',
        ], [
            'name' => 'Montgomery Primary School',
            'town' => 'Birmingham',
            'postcode' => 'B11 1EH',
        ]);

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
                    'academic_year' => '2025/2026',
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

        // Fetch all genre ids from db
        $allGenreIds = Genre::pluck('id');

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
                    'level' => fake()->numberBetween(1, 20),
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

                $students->push($student);
            }

            // assign students to pivot table and give them a school id too
            $classroom->students()->syncWithPivotValues(
                $students->pluck('id')->toArray(), 
                ['school_id' => $school->id]
            );
        }
        
        $this->command->info('Created test user and synced books and classrooms');
        $this->call([BookReviewSeeder::class]);
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