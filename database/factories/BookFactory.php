<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ol_key' => $this->faker->unique()->bothify('BOOK-####-????'),
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'cover_id' => 'Ba1fEQAAQBAJ',
            'ort_level' => 19,
            'ort_colour' => 'Dark Red',
            'description' => $this->faker->paragraph(),
        ];
    }

    public function colour(string $colour, int $level): static
    {
        return $this->state(fn (array $attributes) => [
            'ort_colour' => $colour,
            'ort_level' => $level,
        ]);
    }
}
