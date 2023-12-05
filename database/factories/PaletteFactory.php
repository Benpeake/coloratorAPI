<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaletteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->numberBetween(1, 1000),
            'name' => $this->faker->text(14),
            'hex_colors' => [
                $this->faker->hexColor,
                $this->faker->hexColor,
                $this->faker->hexColor,
            ],
            'public' => $this->faker->boolean,
            'likes' => $this->faker->numberBetween(0, 100),
            'user_id' => User::factory(),
        ];

    }
}
