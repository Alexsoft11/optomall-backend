<?php

namespace Database\Factories;

use App\Models\ProductColor;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductColorFactory extends Factory
{

    protected $model = ProductColor::class;

    public function definition()
    {

        $uuid = Uuid::uuid4()->toString();
        return [
            'uuid' => $uuid,
            'product_id' => \App\Models\Product::factory(),
            'property_name' => 'color',
            'value' => 'rangi',
            'image_url' => $this->faker->imageUrl(),
            'mini_image_url' => $this->faker->imageUrl(),
        ];
    }
}
