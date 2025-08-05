<?php

namespace App\Http\Controllers\User;

use App\Models\Attendance;
use App\Models\BreakTime;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;



class AttendanceController extends Controller
{
    public function create()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $status = 'not_working';

        if ($attendance) {
            if ($attendance->clock_out) {
                $status = 'clocked_out';
            } else {
                $latestBreak = BreakTime::where('attendance_id', $attendance->id)
                    ->whereNull('break_out')
                    ->latest('break_in')
                    ->first();

                if ($latestBreak) {
                    $status = 'on_break';
                } else {
                    $status = 'working';
                }
            }
        }

        return view('user.attendance.punch', compact('now', 'status'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today();
        $now = Carbon::now();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today]
        );

        switch ($request->input('action')) {
            case 'clock_in':
                if (!$attendance->clock_in) {
                    $attendance->clock_in = $now;
                    $attendance->save();
                }
                break;

            case 'clock_out':
                if (!$attendance->clock_out) {
                    $attendance->clock_out = $now;
                    $attendance->save();
                }
                break;

            default:
                return back()->withErrors(['error' => '不正な操作です。']);
        }

        return redirect()->route('attendance.create')->with('success', '操作が完了しました。');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $date = $request->input('date') ?? now()->format('Y-m');

        try {
            $carbonDate = \Carbon\Carbon::createFromFormat('Y-m', $date)->startOfMonth();
        } catch (\Exception $e) {
            $carbonDate = now()->startOfMonth(); // fallback
        }
        $startDate = $carbonDate->copy();
        $endDate = $carbonDate->copy()->endOfMonth();

        // 勤怠データを取得・日付をキーに
        $attendanceMap = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->get()
            ->keyBy('work_date');

        $daysInMonth = collect();

        for($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $attendance = $attendanceMap->get($date->toDateString());

            $dayData = new \stdClass();
            $dayData->date = $date->copy();
            $dayData->day_of_week = $date->locale('ja')->isoFormat('ddd');
            $dayData->is_weekend = $date->isWeekend();

            if($attendance) {
                $totalBreakMinutes = $attendance->breakTimes->sum(function ($break) {
                    if ($break->break_in && $break->break_out) {
                        return Carbon::parse($break->break_out)->diffInMinutes(Carbon::parse($break->break_in));
                    }
                    return 0;
                });
                $attendance->break_time_formatted = sprintf('%02d:%02d', floor($totalBreakMinutes / 60), $totalBreakMinutes % 60);

                // 合計勤務時間
                if ($attendance->clock_in && $attendance->clock_out) {
                    $workMinutes = Carbon::parse($attendance->clock_out)->diffInMinutes(Carbon::parse($attendance->clock_in));
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

        return view('user.attendance.index', [
            'daysInMonth' => $daysInMonth,
            'currentMonth' => $carbonDate,
        ]);
    }
}
