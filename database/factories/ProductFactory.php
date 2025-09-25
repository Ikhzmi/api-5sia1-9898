<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
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
        $users = User::pluck('id')->toArray();
        return [
            'user_id' => fake()->randomElement($users),
            'name' => fake()->company(),
            'is_available' => fake()->boolean(80),
            'stock' => fake()->numberBetween(10, 200),
            'price' => fake()->numberBetween(2000, 200000)
        ];
    }
}