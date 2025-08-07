<?php

namespace Database\Factories;

use App\Models\AttendanceEditRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceEditRequestFactory extends Factory
{
    protected $model = AttendanceEditRequest::class;

    public function definition()
    {
        return [
            'attendance_id' => Attendance::factory(),
            'user_id' => User::factory(),
            'clock_in' => now()->setTime(9, 0, 0),
            'clock_out' => now()->setTime(17, 0, 0),
            'note' => '修正申請のテスト',
            'status' => 'pending',
        ];
    }
}
