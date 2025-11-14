<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminAndWorkerSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
            ]
        );

        User::updateOrCreate(
            ['username' => 'pekerja'],
            [
                'name' => 'Operator',
                'email' => 'pekerja@example.com',
                'role' => 'pekerja',
                'password' => Hash::make('worker123'),
            ]
        );
    }
}
