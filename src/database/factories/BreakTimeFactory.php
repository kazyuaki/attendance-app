<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BreakTimeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $start = $this->faker->dateTimeBetween('12:00', '13:00');
        return [
            'attendance_id' => \App\Models\Attendance::inRandomOrder()->first()->id,
            'break_in' => $start,
            'break_out' => $this->faker->dateTimeBetween($start->format('H:i:s'), '15:00'),
        ];
    }
}
