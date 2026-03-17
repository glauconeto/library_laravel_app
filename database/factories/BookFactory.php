<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'author' => fake()->name(),
            'genre' => fake()->randomElement(['Ficção', 'Romance', 'Terror', 'Fantasia', 'Técnico', 'Biografia', 'História', 'Poesia']),
            'year' => fake()->numberBetween(1900, (int) date('Y')),
            'isbn' => fake()->unique()->isbn13(),
            'description' => fake()->optional()->paragraph(),
            'stock' => fake()->numberBetween(1, 10),
        ];
    }
}
