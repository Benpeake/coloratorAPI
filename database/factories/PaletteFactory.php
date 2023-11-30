<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Palette>
 */
class PaletteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->text(14),
            'hex_colors' => [
                $this->faker->hexColor,
                $this->faker->hexColor,
                $this->faker->hexColor,
            ],
        ];

    }
}
