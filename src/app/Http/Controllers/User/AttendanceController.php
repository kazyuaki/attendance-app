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
                if ($break->break_in && $break->break_out) {
                    return Carbon::parse($break->break_out)->diffInMinutes(Carbon::parse($break->break_in));
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

        return view('user.attendance.index', [
            'attendances' => $attendances,
            'currentMonth' => $carbonDate
        ]);
    }
}
