<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['username' => 'pekerja'],
            [
                'name' => 'Pekerja',
                'email' => 'pekerja@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pekerja',
            ]
        );
    }
}
