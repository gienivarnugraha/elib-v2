<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date_format' => fake()->randomElement(config('core.date_formats')),
            'time_format' => fake()->randomElement(config('core.time_formats')),
            'first_day_of_week' => strval(fake()->numberBetween(1, 6)),
            'currency' => fake()->randomElement(['IDR', 'USD']),
        ];
    }
}
