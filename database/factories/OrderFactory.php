<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // 'user_id' =>  User::all()->random()->id,
            'date_from' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'date_to' => fake()->dateTimeBetween('-1 week', '+1 week'),
            'passcode' => fake()->regexify('[a-zA-Z0-9]{6}'),
        ];
    }
}
