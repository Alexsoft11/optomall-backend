<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPicture;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductPictureFactory extends Factory
{
    protected $model = ProductPicture::class;

    public function definition()
    {
        static $mainPictureSet = false; // Static variable to track if main picture is set

        return [
            'product_id' => Product::factory(),
            'image_url' => $this->faker->imageUrl(),
            'is_main' => !$mainPictureSet ? true : false, // Set is_main to true only for the first picture, false for others
        ];
    }
}
