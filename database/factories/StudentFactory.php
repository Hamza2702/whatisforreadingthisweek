<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Student;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{

    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'active' => true,
            'level' => fake()->numberBetween(0, 20),
            'date_of_birth' => fake()->date(),
            'pfp' => '/images/pfp/' . collect(['lamb.png','cat.png','dog.png','penguin.png','raccoon.png','owl.png','pig.png','wolf.png'])->random(),
        ];
    }
}
