<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Random\RandomException;


class ProductImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws RandomException
     */
    public function definition(): array
    {
        $image = fake()->sha1;
        return [
            'product_id' => mt_rand(1, 50),
            'image_path' => "product_images/$image.jpg",
        ];
    }
}
