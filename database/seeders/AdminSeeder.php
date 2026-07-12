<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         User::updateOrCreate(
            ['email' => 'admin@example.com'], 
            [
                'username'          => 'admin',
                'fullname'          => 'System Administrator',
                'email'             => 'admin@example.com',
                'phone'          => '01600000000', 
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
            ]
        );
    }
}
