<?php

namespace Database\Factories;

use App\Models\ProductColor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductColorFactory extends Factory
{
    protected $model = ProductColor::class;

    public function definition()
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'property_name' => 'color',
            'value' => 'rangi',
            'image_url' => $this->faker->imageUrl(),
            'mini_image_url' => $this->faker->imageUrl(),
        ];
    }
}
