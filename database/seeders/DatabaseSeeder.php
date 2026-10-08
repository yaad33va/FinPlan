<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. Skipped when users already exist, so the Docker
     * container can run it on every start without creating duplicates.
     */
    public function run(): void
    {
        if (User::exists()) {
            $this->command?->warn('The database already has data, seeding skipped (use migrate:fresh --seed to reset).');

            return;
        }

        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            BudgetSeeder::class,
            TransactionSeeder::class,
        ]);
    }
}
