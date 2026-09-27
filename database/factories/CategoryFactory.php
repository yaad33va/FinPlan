<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Groceries', 'Transport', 'Rent', 'Utilities', 'Entertainment', 'Health', 'Education', 'Travel', 'Gifts', 'Clothing']),
            'type' => CategoryType::Expense,
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'name' => fake()->randomElement(['Salary', 'Freelance', 'Dividends']),
            'type' => CategoryType::Income,
        ]);
    }
}
