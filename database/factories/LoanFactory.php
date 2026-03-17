<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanFactory extends Factory
{
    public function definition(): array
    {
        $loanDate = fake()->dateTimeBetween('-30 days', 'now');
        $dueDate = (clone $loanDate)->modify('+14 days');

        return [
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
            'loan_date' => $loanDate->format('Y-m-d'),
            'due_date' => $dueDate->format('Y-m-d'),
            'returned_at' => null,
        ];
    }

    public function returned(): static
    {
        return $this->state(fn (array $attributes) => [
            'returned_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'loan_date' => now()->subDays(30)->format('Y-m-d'),
            'due_date' => now()->subDays(16)->format('Y-m-d'),
            'returned_at' => null,
        ]);
    }
}
