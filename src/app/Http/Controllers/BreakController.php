<?php

// app/Http/Controllers/BreakController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BreakTime;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BreakController extends Controller
{
    public function start(Request $request)
    {
        $user = Auth::user();
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->first();

        if (!$attendance) {
            return back()->with('error', '出勤情報が見つかりません');
        }

        $breakCount = $attendance->breakTimes()->count();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_in' => Carbon::now(),
        ]);

        return back()->with('success', '休憩を開始しました');
    }

    public function end(Request $request)
    {
        $user = Auth::user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', Carbon::today())
            ->first();

        if (!$attendance) {
            return back()->with('error', '出勤情報が見つかりません');
        }

        // 未終了の最新休憩を取得して終了時刻を記録
        $latestBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_out')
            ->latest('break_in')
            ->first();

        if (!$latestBreak) {
            return back()->with('error', '未完了の休憩が見つかりません');
        }

        $latestBreak->update([
            'break_out' => Carbon::now(),
        ]);

        return back()->with('success', '休憩を終了しました');
    }
}
