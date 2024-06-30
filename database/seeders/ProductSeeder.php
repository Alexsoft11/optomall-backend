<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductPicture;
use App\Models\ProductVideo;
use App\Models\ProductColor;
use App\Models\ProductSize;
use App\Models\ProductComposition;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::factory()
            ->count(100)
            ->has(ProductPicture::factory()->count(rand(0, 10)), 'pictures') // 3 pictures per product
            ->has(ProductVideo::factory()->count(1), 'videos') // 2 videos per product
            ->has(ProductColor::factory()->count(rand(1, 14)), 'colors') // 4 colors per product
            ->has(ProductSize::factory()->count(5), 'sizes') // 5 sizes per product
            ->has(ProductComposition::factory()->count(3), 'compositions') // 3 compositions per product
            ->create();
    }
}
