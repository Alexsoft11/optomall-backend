<?php

namespace Database\Seeders;

use App\Models\Exchanges;
use Illuminate\Database\Seeder;

class ExchangeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exchanges = [
            ['currency' => 'kg', 'amount' => '10000'],
            ['currency' => 'yuan', 'amount' => '10000'],
            ['currency' => 'product_percent', 'amount' => '10000'],
        ];

        foreach ($exchanges as $exchange) {
            Exchanges::create($exchange);
        }
    }
}
