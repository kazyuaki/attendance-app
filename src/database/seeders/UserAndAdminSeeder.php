<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AdminUser;
use App\Models\Attendance;
use App\Models\BreakTime;

class UserAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者ユーザー
        AdminUser::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // 固定ログイン情報
        ]);

        // 一般ユーザー
        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password'), // 固定ログイン情報
        ]);

        for ($i = 1; $i < 7; $i++) {
            $date = now()->copy()->subDays($i); // ← 固定日を取得

            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date->toDateString(),
                'clock_in' => $date->copy()->setTime(9, 0),
                'clock_out' => $date->copy()->setTime(17, 0),
            ]);

            // 各日付に2つの休憩を登録（12:00〜12:30、15:00〜15:15）
            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'break_start' => $date->copy()->setTime(12, 0),
                'break_end' => $date->copy()->setTime(12, 30),
            ]);

            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'break_start' => $date->copy()->setTime(15, 0),
                'break_end' => $date->copy()->setTime(15, 15),
            ]);
        }
    }
}
