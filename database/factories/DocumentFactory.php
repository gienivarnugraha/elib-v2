<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $office = fake()->randomElement(['DOA', 'AMO']);
        $aircraft = fake()->randomElement(['235', '212', '332', '412']);
        $type = fake()->randomElement(['EO', 'TD', 'JE', 'ES']);
        $year = fake()->numberBetween('10', '25');
        $number = str_pad(fake()->randomDigitNotNull(), 3, '0', STR_PAD_LEFT);

        if ($type == 'JE') {
            $no = 'JE/'.$number.'/MS1000/'.fake()->month.'/'.fake()->year;
        } elseif ($office == 'DOA') {
            $no = $aircraft.'.AS.'.$type.'.'.$year.'.'.$number;
        } else {
            $no = $type.'-'.$aircraft.'-'.fake()->year.'-'.$number;
        }

        return [
            'no' => $no,
            'office' => $office,
            'type' => $type,
            'subject' => fake()->catchPhrase,
            'reference' => strtoupper(fake()->bothify('#??/#??#/###')),
        ];
    }
}
