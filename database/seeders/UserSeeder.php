<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Demo accounts (also listed in README.md).
     *
     * @var list<array{name: string, email: string, password: string, role: UserRole}>
     */
    public const USERS = [
        ['name' => 'Administratorius', 'email' => 'admin@finplan.lt', 'password' => 'Admin12345', 'role' => UserRole::Admin],
        ['name' => 'Jonas Jonaitis', 'email' => 'jonas@finplan.lt', 'password' => 'Jonas12345', 'role' => UserRole::Member],
        ['name' => 'Ona Onaitytė', 'email' => 'ona@finplan.lt', 'password' => 'Ona123456', 'role' => UserRole::Member],
    ];

    public function run(): void
    {
        foreach (self::USERS as $attributes) {
            $user = new User([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'],
            ]);
            $user->role = $attributes['role'];
            $user->email_verified_at = now();
            $user->save();
        }
    }
}
