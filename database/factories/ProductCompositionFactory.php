<?php

namespace Database\Factories;

use App\Models\ProductComposition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCompositionFactory extends Factory
{
    protected $model = ProductComposition::class;

    public function definition()
    {
        return [
            'product_id' => \App\Models\Product::factory(),
            'property_name' => 'Tarkibiy qismi',
            'value' => $this->faker->word,
        ];
    }
}
