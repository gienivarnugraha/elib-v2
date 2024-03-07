<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manual>
 */
class ManualFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['WDM', 'IPC', 'CMM']),
            'part_number' => strtoupper(fake()->bothify('#??-#?-??-???')),
            'lib_call' => strtoupper(fake()->bothify('#??#??????')),
            'subject' => fake()->catchPhrase,
            'volume' => fake()->randomNumber(4),
            'vendor' => fake()->name,
            'caplist' => fake()->boolean,
            'collector' => fake()->randomElement(['AMO1', 'AMO2', 'AMO3']),
        ];
    }
}
