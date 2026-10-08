<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    /**
     * Monthly budget amount per category name for the last three months and the current month
     * (oldest first), so the dashboard of the current month always has data.
     *
     * @var array<string, list<float>>
     */
    public const BUDGETS = [
        'Atlyginimas' => [1850.00, 1850.00, 1920.00, 1920.00],
        'Papildomos pajamos' => [300.00, 250.00, 300.00, 250.00],
        'Maistas ir buities prekės' => [400.00, 420.00, 400.00, 410.00],
        'Būstas ir komunalinės paslaugos' => [650.00, 650.00, 680.00, 700.00],
        'Transportas' => [120.00, 150.00, 120.00, 130.00],
        'Pramogos ir laisvalaikis' => [150.00, 120.00, 150.00, 140.00],
        'Sveikata ir grožis' => [90.00, 90.00, 100.00, 100.00],
    ];

    /**
     * Budget size of each owner compared with BUDGETS.
     *
     * @var array<string, float>
     */
    protected const OWNER_FACTORS = ['jonas@finplan.lt' => 1.0, 'ona@finplan.lt' => 0.8];

    /**
     * @var list<string>
     */
    protected const NOTES = [
        'Ramesnis mėnuo – daugiau atidėti taupymui.',
        'Atostogų mėnuo – daugiau išlaidų pramogoms.',
        'Grįžimas į įprastą ritmą.',
        'Einamasis mėnuo – sekti išlaidas kas savaitę.',
    ];

    /**
     * The seeded months (Y-m), oldest first: three previous months and the current one.
     *
     * @return list<string>
     */
    public static function months(): array
    {
        return array_map(
            fn (int $monthsAgo) => now()->startOfMonth()->subMonths($monthsAgo)->format('Y-m'),
            [3, 2, 1, 0],
        );
    }

    public function run(): void
    {
        $months = self::months();

        Category::with('user')->get()->each(function (Category $category) use ($months): void {
            $factor = self::OWNER_FACTORS[$category->user->email] ?? 1.0;

            foreach (self::BUDGETS[$category->name] as $index => $amount) {
                $category->budgets()->create([
                    'month' => $months[$index],
                    'amount' => round($amount * $factor, 2),
                    'note' => self::NOTES[$index],
                ]);
            }
        });
    }
}
