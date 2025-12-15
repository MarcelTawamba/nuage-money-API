<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if user already exists
        $existingUser = User::where('email', 'test@example.com')->first();
        
        if ($existingUser) {
            $this->command->info('Test user already exists!');
            return;
        }

        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $this->command->info('Test user created successfully!');
        $this->command->info('Email: test@example.com');
        $this->command->info('Password: password123');
    }
}
