<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        // Delete existing test user if exists
        User::where('email', 'test@test.com')->delete();

        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'phone' => '+998901234567',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'info_filled' => true,
            'email_verified' => true,
        ]);

        $this->command->info('✅ Test user created successfully!');
        $this->command->info('📧 Email: test@test.com');
        $this->command->info('🔑 Password: password123');
    }
}
