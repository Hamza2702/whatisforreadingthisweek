<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->command->info('Importing schools');
        Artisan::call('schools:import');

        // get a random school to assign to test user
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
        
        $this->command->info('Created test user');
    }
}
