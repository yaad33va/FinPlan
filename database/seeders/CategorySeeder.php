<?php

namespace Database\Seeders;

use App\Enums\CategoryType;
use App\Models\User;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Categories of every member, keyed by the owner's email.
     *
     * @var array<string, list<array{name: string, type: CategoryType, description: string, color: string}>>
     */
    public const CATEGORIES = [
        'jonas@finplan.lt' => [
            ['name' => 'Atlyginimas', 'type' => CategoryType::Income, 'description' => 'Mėnesinis darbo užmokestis „į rankas“ iš pagrindinės darbovietės.', 'color' => '#2E7D32'],
            ['name' => 'Papildomos pajamos', 'type' => CategoryType::Income, 'description' => 'Laisvai samdomo darbo, daiktų pardavimo ir kitos nereguliarios pajamos.', 'color' => '#66BB6A'],
            ['name' => 'Maistas ir buities prekės', 'type' => CategoryType::Expense, 'description' => 'Pirkiniai prekybos centruose, turguje ir buitinės chemijos prekės.', 'color' => '#EF6C00'],
            ['name' => 'Būstas ir komunalinės paslaugos', 'type' => CategoryType::Expense, 'description' => 'Buto nuoma, elektra, šildymas, vanduo ir internetas.', 'color' => '#5D4037'],
            ['name' => 'Transportas', 'type' => CategoryType::Expense, 'description' => 'Viešasis transportas, kuras, pavėžėjimo paslaugos ir automobilio priežiūra.', 'color' => '#1565C0'],
            ['name' => 'Pramogos ir laisvalaikis', 'type' => CategoryType::Expense, 'description' => 'Kinas, koncertai, kavinės, prenumeratos ir pomėgiai.', 'color' => '#8E24AA'],
            ['name' => 'Sveikata ir grožis', 'type' => CategoryType::Expense, 'description' => 'Vaistinė, gydytojų konsultacijos, sporto klubas ir kirpykla.', 'color' => '#D81B60'],
        ],
        'ona@finplan.lt' => [
            ['name' => 'Atlyginimas', 'type' => CategoryType::Income, 'description' => 'Darbo užmokestis iš mokyklos, kurioje dirbu mokytoja.', 'color' => '#00897B'],
            ['name' => 'Maistas ir buities prekės', 'type' => CategoryType::Expense, 'description' => 'Savaitiniai apsipirkimai ir namų priežiūros prekės.', 'color' => '#F9A825'],
            ['name' => 'Transportas', 'type' => CategoryType::Expense, 'description' => 'Autobuso bilietai ir kelionės pas tėvus į Vilnių.', 'color' => '#0277BD'],
            ['name' => 'Pramogos ir laisvalaikis', 'type' => CategoryType::Expense, 'description' => 'Teatras, knygos ir susitikimai su draugėmis kavinėse.', 'color' => '#6A1B9A'],
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $email => $categories) {
            $owner = User::where('email', $email)->firstOrFail();

            foreach ($categories as $category) {
                $owner->categories()->create($category);
            }
        }
    }
}
