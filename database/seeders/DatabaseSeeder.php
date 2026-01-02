<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use Illuminate\Support\Facades\Artisan;
use App\Models\Classroom;
use App\Models\Student;

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
                
        $this->command->info('Importing schools');
        Artisan::call('schools:import');
        $this->command->info('Schools imported');

        $school = School::inRandomOrder()->first();

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

        $school = School::firstOrCreate([
            'urn' => '138864',
        ], [
            'name' => 'Montgomery Primary School',
            'town' => 'Birmingham',
            'postcode' => 'B11 1EH',
        ]);

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

        $classrooms = collect([3, 4, 5, 6])->map(fn ($year)
            => Classroom::firstOrCreate([
                'school_id' => $school->id,
                'teacher_id' => $teacher->id,
                'year_group' => $year,
                'name' => "Year $year",
                'academic_year' => '2025/2026',
                'active' => true,
            ])
        );
        
        // Create students and assign to classrooms 
        foreach ($classrooms as $classroom){
            // Create students
            $students = Student::factory()->count(rand(20,30))->create([
                'school_id' => $school->id,
                'active' => true,
            ]);

            foreach ($students as $student) {
                $username = $this->createStudentUsername();
                
                $user = User::factory()->create([
                    'username' => $username,
                    'name' => $student->first_name . ' ' . $student->last_name,
                    'email' => $username . '@example.com',
                    'password' => 'password',
                    'phone' => '07' . rand(100000000, 999999999),
                    'role' => 'Student',
                    'school_id' => $school->id,
                ]);

                // Link student to user
                $student->user_id = $user->id;
                $student->save();
            }

            // assign students to classroom
            $classroom->students()->attach($students->pluck('id'));
        }       

        $this->command->info('Created test user');

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
