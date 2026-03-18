<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use Illuminate\Support\Facades\Artisan;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Genre; // <-- Added Genre model

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // get a random school to assign to test user
        

        // KS1 - reception to year 2 (animals)
        // KS2 - year 3 to year 6 (authors)
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

        // Testuser
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

        // Montgomery teacher
        $teacher = User::factory()->create([
            'username' => 'montgomery',
            'name' => 'Montgomery Teacher',
            'email' => 'testteacher@example.com',
            'phone' => '07123456789',
            'role' => 'Teacher',
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

        // Create students and assign to classrooms
        $this->command->info('Creating students and assigning genres...');
        foreach ($classrooms as $classroom) {

            $students = collect();

            // 20-30 students per class
            foreach (range(1, rand(20, 30)) as $_) {

                // generate names once
                $username = $this->createStudentUsername();
                $firstName = fake()->firstName();
                $lastName = fake()->lastName();

                // assign names to the user
                $user = User::factory()->create([
                    'username' => $username,
                    'name' => $firstName . ' ' . $lastName,
                    'email' => $username . '@example.com',
                    'password' => 'password',
                    'phone' => '07' . rand(100000000, 999999999),
                    'role' => 'Student',
                    'school_id' => $school->id,
                ]);

                // create students (assign same names to student profiles)
                $student = $user->student()->create([
                    'school_id' => $school->id,
                    'classroom_id' => $classroom->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'level' => fake()->numberBetween(1, 20),
                    'date_of_birth' => now()->subYears(5 + $classroom->year_group)->subDays(rand(0, 365)),
                    'pfp' => '/images/pfp/' . collect(['lamb.png','cat.png','dog.png','penguin.png','raccoon.png','owl.png','pig.png','wolf.png'])->random(),
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
    }

    // Create student usernames
    protected function createStudentUsername(): string
    {
        $colours = collect([
            'Red', 'Blue', 'Green', 'Yellow', 'Purple', 'Orange', 'Pink', 'Brown', 'Black', 'White', 'Grey',
            'Gold', 'Silver', 'Cyan', 'Magenta', 'Lime', 'Teal', 'Navy', 'Maroon', 'Olive', 'Coral', 'Turquoise',
            'Violet', 'Indigo', 'Amber', 'Crimson', 'Azure', 'Beige', 'Lavender', 'Mint', 'Peach', 'Salmon', 'Tan',
            'Chocolate', 'Plum', 'Rose', 'Sapphire', 'Emerald',
        ]);

        $animals = collect([
            'Lion', 'Tiger', 'Bear', 'Wolf', 'Fox', 'Eagle', 'Hawk', 'Shark', 'Dolphin', 'Whale', 'Penguin',
            'Kangaroo', 'Panda', 'Giraffe', 'Zebra', 'Elephant', 'Cheetah', 'Leopard', 'Rabbit', 'Deer',
            'Otter', 'Raccoon', 'Squirrel', 'Badger', 'Hedgehog', 'Turtle', 'Frog', 'Toad', 'Snake', 'Lizard',
            'Butterfly', 'Bee', 'Ant', 'Dragonfly', 'Ladybug', 'Cat', 'Dog', 'Mouse', 'Rat', 'Hamster', 'Raccoon',
            'Owl', 'Parrot', 'Flamingo', 'Peacock',
        ]);

    $username = $colours->random() . $animals->random() . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

    return $username;
    }
}