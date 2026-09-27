<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use App\Models\Budget;
use App\Models\Transaction;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'budget_id' => Budget::factory(),
            'amount' => fake()->randomFloat(2, 1, 200),
            'occurred_on' => fn (array $attributes) => $this->dateWithinBudgetMonth($attributes['budget_id']),
            'description' => fake()->sentence(3),
            'merchant' => fake()->optional()->company(),
            'payment_method' => fake()->randomElement(PaymentMethod::cases()),
        ];
    }

    protected function dateWithinBudgetMonth(int|Budget $budget): string
    {
        $budget = $budget instanceof Budget ? $budget : Budget::findOrFail($budget);
        $start = CarbonImmutable::createFromFormat('!Y-m', $budget->month);

        return $start->addDays(fake()->numberBetween(0, $start->daysInMonth - 1))->format('Y-m-d');
    }
}
