<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function create()
    {
        // 出勤登録画面の表示
        return view('user.attendance.punch');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $alreadyPunched = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->exists();

        if ($alreadyPunched) {
            return redirect()->back()->withErrors(['error' => '本日はすでに出勤登録済みです。']);
        }

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today,
            'clock_in' => Carbon::now()->toTimeString(),
        ]);
        return redirect()->route('attendance.index')->with('success', '出勤登録が完了しました。');
    }
}
