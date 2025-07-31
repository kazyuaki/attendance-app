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

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $carbonDate->year)
            ->whereMonth('work_date', $carbonDate->month)
            ->orderBy('work_date', 'asc')
            ->get();

        foreach ($attendances as $attendance) {
            // 曜日
            $attendance->day_of_week = Carbon::parse($attendance->work_date)->locale('ja')->translatedFormat('ddd');

            // 休憩時間（分単位で計算）
            $totalBreakMinutes = $attendance->breakTimes->sum(function ($break) {
                if ($break->break_start && $break->break_end) {
                    return Carbon::parse($break->break_end)->diffInMinutes(Carbon::parse($break->break_start));
                }
                return 0;
            });

            $attendance->break_time_formatted = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

            // 合計勤務時間（出勤～退勤 - 休憩）
            if ($attendance->clock_in && $attendance->clock_out) {
                $workMinutes = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in));
                $netMinutes = max($workMinutes - $totalBreakMinutes, 0);
                $attendance->total_work_time = sprintf('%02d:%02d', floor($netMinutes / 60), $netMinutes % 60);
            } else {
                $attendance->total_work_time = '--:--';
            }
        }

        return view('admin.staff.attendance', [
            'attendances' => $attendances,
            'currentMonth' => $carbonDate,
            'user' => $user
        ]);
    }
}
