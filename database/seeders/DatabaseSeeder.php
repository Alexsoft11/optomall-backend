<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'email_verified' => true,
            'info_filled' => true,
            'email_verified_at' => now(),
            'password' =>  bcrypt('12345678'),
            'confirm_password' =>  bcrypt('12345678'),
        ]);
        $this->call(OrderStatusSeeder::class);
        $this->call(OrderSubStatusSeeder::class);
        $this->call(ExchangeSeeder::class);
        // User::factory(10)->create();
        $this->call(TaobaoCategorySeeder::class);
        // $this->call(ProductCategorySeeder::class);
        // $this->call(ProductSeeder::class);
        // $this->call(ProductsSeeder::class);
        // $this->call(SliderSeeder::class);
    }
}
