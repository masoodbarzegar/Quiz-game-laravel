<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
        $users =[
            [
                'name' => 'Masood Barzegar',
                'email' => 'mb@quizgame.com',
                'password' => Hash::make('M123'), // Change this in production!
                'role' => 'manager',
            ],
            [
                'name' => 'Corrector 1',
                'email' => 'Corrector1@quizgame.com',
                'password' => Hash::make('M123'), // Change this in production!
                'role' => 'corrector',
            ],
            [
                'name' => 'Questioner 1',
                'email' => 'Questioner1@quizgame.com',
                'password' => Hash::make('M123'), // Change this in production!
                'role' => 'general',
            ],
        ];
         // Create the questions with mixed statuses to demonstrate corrector's capabilities
        foreach ($users as $index => $user) {

            $user = User::create([
                ...$user,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);
        }
    }
} 