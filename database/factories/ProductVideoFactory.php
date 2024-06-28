<?php

namespace Database\Factories;

use App\Models\ProductVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVideoFactory extends Factory
{
    protected $model = ProductVideo::class;

    public function definition()
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'preview_url' => $this->faker->imageUrl(),
            'url' => $this->faker->url,
        ];
    }
}
