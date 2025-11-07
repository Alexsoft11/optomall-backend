<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $price = $this->faker->randomFloat(2, 10, 1000);
        $discountPercent = $this->faker->optional(0.5, 0)->randomFloat(2, 5, 50); // 50% chance of having a discount, between 5% and 50%
        $discountedPrice = $discountPercent ? $price - ($price * $discountPercent / 100) : null;

        return [
            'product_category_id' => ProductCategory::inRandomOrder()->first()->id,
            'title' => $this->faker->sentence,
            'price' => $price,
            'image' => $this->faker->imageUrl(),
            'location' => $this->faker->city,
            'product_url' => $this->faker->url,
            'description' => $this->faker->paragraph,
            'quantity' => $this->faker->numberBetween(1, 100),
            'discount_percent' => $discountPercent,
            'discounted_price' => $discountedPrice,
            'rating' => $this->faker->optional(0.8, null)->randomFloat(2, 1, 5),
            'sold' => $this->faker->numberBetween(10, 500),
        ];
    }
}
