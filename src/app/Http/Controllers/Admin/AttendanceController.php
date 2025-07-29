<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Http\Requests\UpdateAttendanceRequest;


class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with(['user', 'breakTimes', 'attendanceEditRequests'])->latest()->get();
        return view('admin.attendance.index', compact('attendances'));
    }

    // 詳細表示（管理者側）
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

        $pendingRequest = AttendanceEditRequest::where('attendance_id', $id)
            ->where('status', 'pending')
            ->first(); 

        $breaks = $attendance->breakTimes->groupBy('break_number');
        $break1 = $breaks->get(1)?->first();
        $break2 = $breaks->get(2)?->first();

        return view('admin.attendance.show', compact('attendance', 'break1', 'break2', 'pendingRequest'));
    }


    public function update(UpdateAttendanceRequest $request, $id) {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        $date = $attendance->work_date;

        $validated = $request->validated();
        // 勤怠本体
        $attendance->clock_in = $validated['clock_in'] ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['clock_in']}") : null;
        $attendance->clock_out = $validated['clock_out'] ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['clock_out']}") : null;
        $attendance->note = $validated['note'] ?? null;
        $attendance->save();

        // 休憩1
        $break1 = $attendance->breakTimes()->where('break_number', 1)->first();
        if ($break1) {
            $break1->break_start = $validated['break1_start'] ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['break1_start']}") : null;
            $break1->break_end = $validated['break1_end'] ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['break1_end']}") : null;
            $break1->save();
        }

        // 休憩2
        $break2 = $attendance->breakTimes()->where('break_number', 2)->first();
        if ($break2) {
            $break2->break_start = $validated['break2_start'] ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['break2_start']}") : null;
            $break2->break_end = $validated['break2_end'] ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['break2_end']}") : null;
            $break2->save();
        }

        return redirect()->route('admin.attendances.show', $attendance->id)
            ->with('success', '勤怠データを更新しました。');
    }
}
