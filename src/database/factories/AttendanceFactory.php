<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'work_date' => $this->faker->dateTimeBetween('-10 days', 'now')->format('Y-m-d'),
            'clock_in' => $in = $this->faker->dateTimeBetween('08:00', '10:00'),
            'clock_out' => $this->faker->dateTimeBetween($in->format('H:i:s'), '19:00'),
            'note' => $this->faker->optional()->sentence,
        ];
    }
}
