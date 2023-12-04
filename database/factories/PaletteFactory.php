<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PaletteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->text(14),
            'hex_colors' => [
                $this->faker->hexColor,
                $this->faker->hexColor,
                $this->faker->hexColor,
            ],
            'public' => $this->faker->boolean,
            'votes' => $this->faker->numberBetween(0, 100),
            'user_id' => User::factory(),
        ];

    }
}
