<?php

namespace Database\Factories;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Budget>
 */
class BudgetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'month' => fake()->unique()->dateTimeBetween('-2 years')->format('Y-m'),
            'amount' => fake()->randomFloat(2, 100, 1500),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
