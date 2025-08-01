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

            $clockIn = $date->copy()->setTime(9, 0)->addMinutes(rand(-15, 15));
            $clockOut = $date->copy()->setTime(17, 0)->addMinutes(rand(0, 60));

            $attendance = Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date->toDateString(),
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
            ]);

            // 休憩1（ランチ） 11:45〜12:15 スタート → 30分休憩
            $break1Start = $date->copy()->setTime(12, 0)->addMinutes(rand(-15, 15));
            $break1End = (clone $break1Start)->addMinutes(30);

            // 休憩2（午後） 14:45〜15:15 スタート → 15分休憩
            $break2Start = $date->copy()->setTime(15, 0)->addMinutes(rand(-15, 0));
            $break2End = (clone $break2Start)->addMinutes(15);

            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'break_in' => $break1Start,
                'break_out' => $break1End,
            ]);

            BreakTime::factory()->create([
                'attendance_id' => $attendance->id,
                'break_in' => $break2Start,
                'break_out' => $break2End,
            ]);
        }
    }
}
