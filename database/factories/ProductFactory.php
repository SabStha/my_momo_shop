<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 8, 20),
            'image' => 'images/momos/steamed-momo.jpg',
            'category' => $this->faker->randomElement(['Chicken', 'Vegetarian', 'Pork', 'Beef']),
            'active' => true,
        ];
    }
} 