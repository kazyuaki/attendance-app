<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
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
                    ->whereNull('break_end')
                    ->latest('break_start')
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

            case 'break_start':
                $attendance->breakTimes()->create([
                    'break_start' => $now,
                ]);
                break;

            case 'break_end':
                $latestBreak = $attendance->breakTimes()
                    ->whereNull('break_end')
                    ->latest('break_start')
                    ->first();

                if ($latestBreak) {
                    $latestBreak->break_end = $now;
                    $latestBreak->save();
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

    public function index()
    {
        $user = Auth::user();

        $attendances = Attendance::where('user_id', $user->id)
            ->orderBy('work_date', 'desc')
            ->get();

        foreach ($attendances as $attendance) {
            $attendance->day_of_week = Carbon::parse($attendance->work_date)->locale('ja')->translatedFormat('ddd');
        }

        return view('user.attendance.index', compact('attendances'));
    }
}
