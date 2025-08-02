<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AdminUser;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class UserAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者ユーザー
        AdminUser::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // 一般ユーザー
        $user = User::factory()->create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
        ]);

        // 2025年9月1日〜30日分の勤怠データ作成
        $startDate = Carbon::create(2025, 9, 1);
        $endDate = Carbon::create(2025, 9, 30);

        for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
            // 土日はスキップ（任意）
            if ($date->isWeekend()) {
                continue;
            }

            $clockIn = $date->copy()->setTime(9, 0)->addMinutes(rand(-10, 10));
            $clockOut = $date->copy()->setTime(18, 0)->addMinutes(rand(0, 30));

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date->toDateString(),
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
            ]);

            // ランチ休憩（12:00〜12:45）
            $break1Start = $date->copy()->setTime(12, 0);
            $break1End = $break1Start->copy()->addMinutes(45);

            // 午後休憩（15:00〜15:15）
            $break2Start = $date->copy()->setTime(15, 0);
            $break2End = $break2Start->copy()->addMinutes(15);

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_in' => $break1Start,
                'break_out' => $break1End,
            ]);

            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_in' => $break2Start,
                'break_out' => $break2End,
            ]);
        }
    }
}
