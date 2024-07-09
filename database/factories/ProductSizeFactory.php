<?php

namespace Database\Factories;

use App\Models\ProductSize;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductSizeFactory extends Factory
{
    protected $model = ProductSize::class;

    public function definition()
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'property_name' => 'size',
            'value' => $this->faker->word,
        ];
    }
}
