<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Random\RandomException;


class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'category_id' => random_int(1, 4),
            'brand_id' => null,
            'title' => fake()->sentence(),
            'code' => fake()->unique()->randomNumber(4),
            'price' => random_int(100, 1000) * 100,
            'inventory' => random_int(0, 25),
            'discount_percent' => fake()->numberBetween(0, 10),
            'special_offer' => fake()->boolean(10),
            'discount_rules' => json_encode([
                '1' => '0',
                '5' => '3',
            ]),
            'description' => fake()->randomHtml(),
            'technical_description' => fake()->randomHtml(),
            'faq' => fake()->randomHtml(),
        ];
    }
}
