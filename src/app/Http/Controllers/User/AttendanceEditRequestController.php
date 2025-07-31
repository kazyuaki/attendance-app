<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\AttendanceEditRequestForm;
use App\Http\Controllers\Controller;
use App\Models\AttendanceEditRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceEditRequestController extends Controller
{
    public function store(AttendanceEditRequestForm $request)
    {
        $validated = $request->validated();

        // 勤怠日を取得
        $attendance = Attendance::findOrFail($validated['attendance_id']);
        $date = $attendance->work_date; // 'Y-m-d'

        // 時刻入力を datetime に変換（nullならそのまま）
        $clock_in = $validated['clock_in'] ? Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['clock_in']}") : null;
        $clock_out = $validated['clock_out'] ? Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['clock_out']}") : null;
        $break1_start = $validated['break1_start'] ? Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['break1_start']}") : null;
        $break1_end = $validated['break1_end'] ? Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['break1_end']}") : null;
        $break2_start = $validated['break2_start'] ? Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['break2_start']}") : null;
        $break2_end = $validated['break2_end'] ? Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['break2_end']}") : null;

        AttendanceEditRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $validated['attendance_id'],
            'clock_in' => $clock_in,
            'clock_out' => $clock_out,
            'break1_start' => $break1_start,
            'break1_end' => $break1_end,
            'break2_start' => $break2_start,
            'break2_end' => $break2_end,
            'note' => $validated['note'],
        ]);

        return redirect()->route('attendance.show', ['id' => $validated['attendance_id']])
            ->with('success', '修正申請を送信しました。');
    }

    public function index(Request $request)
    {
        $status = $request->input('status','pending');
        $requests = AttendanceEditRequest::with(['attendance', 'user'])
            ->where('user_id', Auth::id())
            ->where('status', $status)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('user.request.index', compact('requests','status'));
    }


}
