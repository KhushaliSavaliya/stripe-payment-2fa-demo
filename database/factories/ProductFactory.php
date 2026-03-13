<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $randomId = rand(1, 1000);

        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(10),
            'price' => $this->faker->numberBetween(1000, 9000),
            'image' => "https://picsum.photos/seed/{$randomId}/600/400",
        ];
    }
}
