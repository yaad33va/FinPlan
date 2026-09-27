<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Models\Budget;
use Illuminate\Database\Seeder;

class TransactionSeeder extends Seeder
{
    /**
     * Typical monthly transactions per category: [day, amount, description, merchant, payment method].
     * Amounts vary slightly by month so that every budget has a different usage.
     *
     * @var array<string, list<array{0: int, 1: float, 2: string, 3: string|null, 4: PaymentMethod}>>
     */
    protected const TEMPLATES = [
        'Atlyginimas' => [
            [10, 1850.00, 'Mėnesio darbo užmokestis', 'UAB „Tech Solutions“', PaymentMethod::BankTransfer],
        ],
        'Papildomos pajamos' => [
            [6, 120.00, 'Svetainės maketavimo užsakymas', 'MB „Kūrybos dirbtuvės“', PaymentMethod::BankTransfer],
            [14, 45.00, 'Parduotas dviratis per Skelbiu.lt', null, PaymentMethod::Cash],
            [22, 80.00, 'Korepetitoriaus pamokos (matematika)', null, PaymentMethod::Cash],
        ],
        'Maistas ir buities prekės' => [
            [2, 62.35, 'Savaitės maisto pirkiniai', 'Maxima', PaymentMethod::Card],
            [9, 54.80, 'Savaitės maisto pirkiniai', 'Lidl', PaymentMethod::Card],
            [12, 18.40, 'Daržovės ir vaisiai turguje', 'Halės turgus', PaymentMethod::Cash],
            [16, 71.15, 'Savaitės maisto pirkiniai', 'Rimi', PaymentMethod::Card],
            [23, 58.90, 'Savaitės maisto pirkiniai', 'Iki', PaymentMethod::Card],
            [27, 24.60, 'Buitinė chemija ir higienos prekės', 'Drogas', PaymentMethod::Card],
        ],
        'Būstas ir komunalinės paslaugos' => [
            [1, 450.00, 'Buto nuoma', 'Nuomotojas J. Petrauskas', PaymentMethod::BankTransfer],
            [15, 38.70, 'Elektra', 'Ignitis', PaymentMethod::BankTransfer],
            [15, 17.20, 'Šaltas vanduo', 'Kauno vandenys', PaymentMethod::BankTransfer],
            [18, 24.99, 'Namų internetas', 'Telia', PaymentMethod::Card],
            [20, 52.00, 'Namo administravimas ir šildymas', 'Kauno energija', PaymentMethod::BankTransfer],
        ],
        'Transportas' => [
            [1, 29.00, 'Mėnesinis viešojo transporto bilietas', 'Kauno viešasis transportas', PaymentMethod::Card],
            [8, 45.20, 'Degalai', 'Circle K', PaymentMethod::Card],
            [19, 8.60, 'Kelionė taksi', 'Bolt', PaymentMethod::Card],
            [26, 12.40, 'Autobuso bilietas Kaunas–Vilnius', 'Kautra', PaymentMethod::Card],
        ],
        'Pramogos ir laisvalaikis' => [
            [5, 13.99, 'Muzikos prenumerata', 'Spotify', PaymentMethod::Card],
            [11, 17.50, 'Kino bilietai (2 vnt.)', 'Forum Cinemas', PaymentMethod::Card],
            [17, 32.40, 'Vakarienė su draugais', 'Restoranas „Motiejaus kepyklėlė“', PaymentMethod::Card],
            [24, 45.00, 'Koncerto bilietas', 'Bilietai.lt', PaymentMethod::Card],
        ],
        'Sveikata ir grožis' => [
            [4, 35.00, 'Sporto klubo abonementas', 'Impuls', PaymentMethod::Card],
            [13, 12.85, 'Vitaminai ir vaistai', 'Eurovaistinė', PaymentMethod::Card],
            [21, 25.00, 'Kirpimas', 'Kirpykla „Žirklės“', PaymentMethod::Cash],
        ],
    ];

    /**
     * Multiplier applied to template amounts per month (August has a bigger entertainment spend).
     *
     * @var array<string, float>
     */
    protected const MONTH_FACTORS = ['2026-07' => 1.05, '2026-08' => 0.95, '2026-09' => 0.90];

    public function run(): void
    {
        Budget::with('category')->get()->each(function (Budget $budget): void {
            $factor = self::MONTH_FACTORS[$budget->month];

            if ($budget->category->name === 'Pramogos ir laisvalaikis' && $budget->month === '2026-08') {
                $factor = 1.25;
            }

            foreach (self::TEMPLATES[$budget->category->name] as [$day, $amount, $description, $merchant, $method]) {
                if ($budget->category->name === 'Atlyginimas') {
                    $factor = 1.0;
                    $amount = (float) $budget->amount;
                }

                $budget->transactions()->create([
                    'occurred_on' => sprintf('%s-%02d', $budget->month, $day),
                    'amount' => round($amount * $factor, 2),
                    'description' => $description,
                    'merchant' => $merchant,
                    'payment_method' => $method,
                ]);
            }
        });
    }
}
