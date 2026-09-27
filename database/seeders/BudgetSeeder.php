<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    /**
     * Monthly budget amount per category name for July, August and September 2026.
     *
     * @var array<string, array<string, float>>
     */
    public const BUDGETS = [
        'Atlyginimas' => ['2026-07' => 1850.00, '2026-08' => 1850.00, '2026-09' => 1920.00],
        'Papildomos pajamos' => ['2026-07' => 300.00, '2026-08' => 250.00, '2026-09' => 300.00],
        'Maistas ir buities prekės' => ['2026-07' => 400.00, '2026-08' => 420.00, '2026-09' => 400.00],
        'Būstas ir komunalinės paslaugos' => ['2026-07' => 650.00, '2026-08' => 650.00, '2026-09' => 680.00],
        'Transportas' => ['2026-07' => 120.00, '2026-08' => 150.00, '2026-09' => 120.00],
        'Pramogos ir laisvalaikis' => ['2026-07' => 150.00, '2026-08' => 120.00, '2026-09' => 150.00],
        'Sveikata ir grožis' => ['2026-07' => 90.00, '2026-08' => 90.00, '2026-09' => 100.00],
    ];

    /**
     * @var array<string, string>
     */
    protected const NOTES = [
        '2026-07' => 'Liepa – atostogų mėnuo.',
        '2026-08' => 'Rugpjūtis – pasiruošimas rudeniui.',
        '2026-09' => 'Rugsėjis – grįžimas į įprastą ritmą.',
    ];

    public function run(): void
    {
        foreach (self::BUDGETS as $categoryName => $months) {
            $category = Category::where('name', $categoryName)->firstOrFail();

            foreach ($months as $month => $amount) {
                $category->budgets()->create([
                    'month' => $month,
                    'amount' => $amount,
                    'note' => self::NOTES[$month],
                ]);
            }
        }
    }
}
