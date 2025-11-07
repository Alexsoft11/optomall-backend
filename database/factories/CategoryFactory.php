<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        return [
            'is_active' => $this->faker->boolean,
            'name' => $this->faker->word,
            'image' => $this->faker->imageUrl(),
        ];
    }
}
