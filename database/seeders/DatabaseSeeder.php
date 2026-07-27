<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Admin User
        User::updateOrCreate(
            ['email' => 'admin@ipromind.com'],
            [
                'name' => 'iPromind Admin',
                'password' => bcrypt('iPromind@9547'),
                'user_type' => 'admin',
                'status' => 1,
                'email_verified_at' => now(),
            ]
        );

        // Seed Standard User
        User::updateOrCreate(
            ['email' => 'ramkrishna@ipromind.com'],
            [
                'name' => 'Ramkrishna Maity',
                'password' => bcrypt('iPromind@9547'),
                'user_type' => 'user',
                'status' => 1,
                'email_verified_at' => now(),
            ]
        );
    }
}
