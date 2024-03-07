<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Aircraft>
 */
class AircraftFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['C212', 'CN235', 'AS365', 'CN295']),
            'serial_num' => strtoupper(fake()->bothify('#??#?')),
            'reg_code' => strtoupper(fake()->bothify('#?????')),
            'effectivity' => fake()->randomNumber(5),
            'owner' => fake()->randomElement(['TNI AU', 'TNI AL', 'TNI AD', 'CIVIL']),
            'manuf_date' => fake()->numberBetween(1990, 2000), //
        ];
    }
}
