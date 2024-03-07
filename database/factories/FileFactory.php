<?php

namespace Database\Factories;

use App\Models\Revision;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(array('document', 'manual')),
            'filename' => fake()->bothify('#??/#??#.###'),
            'revision_id' => Revision::factory(),
        ];
    }
}
