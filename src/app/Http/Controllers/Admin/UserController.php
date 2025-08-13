<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $users = User::withCount('attendances')->get();

        return view('admin.staff.index', compact('users'));
    }

    public function userAttendances(Request $request, User $user)
    {
        $date = trim($request->input('date') ?? now()->format('Y-m'));
        $carbonDate = \Carbon\Carbon::parse($date)->startOfMonth();
        $startDate = $carbonDate->copy();
        $endDate = $carbonDate->copy()->endOfMonth();

        // 勤怠データを日付でキーにする
        $attendanceMap = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->mapWithKeys(function ($attendance) {
                $key = $attendance->work_date instanceof \Carbon\Carbon
                    ? $attendance->work_date->toDateString()
                    : \Carbon\Carbon::parse($attendance->work_date)->toDateString();
                return [$key => $attendance];
            });

        $daysInMonth = collect();

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayData = new \stdClass();
            $dayData->date = $date->copy();
            $dayData->day_of_week = $date->locale('ja')->isoFormat('ddd');
            $dayData->is_weekend = $date->isWeekend();

            $attendance = $attendanceMap->get($date->toDateString());

            if ($attendance) {
                // 休憩時間
                $totalBreakMinutes = $attendance->breakTimes->sum(function ($break) {
                    if ($break->break_in && $break->break_out) {
                        return \Carbon\Carbon::parse($break->break_out)->diffInMinutes(\Carbon\Carbon::parse($break->break_in));
                    }
                    return 0;
                });

                $attendance->break_time_formatted = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                // 勤務時間
                if ($attendance->clock_in && $attendance->clock_out) {
                    $workMinutes = \Carbon\Carbon::parse($attendance->clock_out)->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_in));
                    $netMinutes = max($workMinutes - $totalBreakMinutes, 0);
                    $attendance->total_work_time = sprintf('%02d:%02d', floor($netMinutes / 60), $netMinutes % 60);
                } else {
                    $attendance->total_work_time = '--:--';
                }

                $dayData->attendance = $attendance;
            } else {
                $dayData->attendance = null;
            }

            $daysInMonth->push($dayData);
        }

        return view('admin.staff.attendance', [
            'daysInMonth' => $daysInMonth,
            'currentMonth' => $carbonDate,
            'user' => $user
        ]);
    }
}
