<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Default users per role. Password: "password".
     */
    public const USERS = [
        'super admin' => ['admin@example.com', 'Admin System'],
        'manager' => ['manager@example.com', 'Manager'],
        'warehouse' => ['warehouse@example.com', 'Warehouse Staff'],
        'purchasing' => ['purchasing@example.com', 'Purchasing Staff'],
        'sales' => ['sales@example.com', 'Sales Staff'],
        'finance' => ['finance@example.com', 'Finance Staff'],
        'viewer' => ['viewer@example.com', 'Viewer'],
    ];

    public function run(): void
    {
        foreach (self::USERS as $role => [$email, $name]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            $user->syncRoles([$role]);
        }
    }
}
