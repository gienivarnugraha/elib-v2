<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     * {"path":"\/deals\/58?comment_id=2&section=notes&resourceId=86","lang":{"key":"notifications.user_mentioned","attrs":{"name":"Admin"}}}
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'data' => [
            ],
        ];
    }
}
