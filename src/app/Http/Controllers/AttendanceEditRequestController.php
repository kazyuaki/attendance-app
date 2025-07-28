<?php

namespace App\Http\Controllers;

use App\Http\Requests\AttendanceEditRequestForm;
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

    public function index()
    {
        $requests = AttendanceEditRequest::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.request.index', compact('requests'));
    }

    // 詳細表示（管理者側）
    public function show($id)
    {
        $request = AttendanceEditRequest::with('attendance.user')->findOrFail($id);

        return view('admin.requests.show', compact('request'));
    }

    // 承認処理（管理者側）
    public function approve(Request $request, $id)
    {
        $editRequest = AttendanceEditRequest::findOrFail($id);

        // 出勤・退勤の更新（例）
        $attendance = $editRequest->attendance;
        $attendance->clock_in = $editRequest->edited_clock_in;
        $attendance->clock_out = $editRequest->edited_clock_out;
        $attendance->note = $editRequest->edited_note;
        $attendance->save();

        // 休憩1を更新
        $break1 = $attendance->breaks()->where('break_number', 1)->first();
        if ($break1 && $editRequest->edited_break1_start && $editRequest->edited_break1_end) {
            $break1->break_start = $editRequest->edited_break1_start;
            $break1->break_end = $editRequest->edited_break1_end;
            $break1->save();
        }

        // 休憩2を更新（必要なら）
        $break2 = $attendance->breaks()->where('break_number', 2)->first();
        if ($break2 && $editRequest->edited_break2_start && $editRequest->edited_break2_end) {
            $break2->break_start = $editRequest->edited_break2_start;
            $break2->break_end = $editRequest->edited_break2_end;
            $break2->save();
        }

        // 修正申請ステータス更新
        $editRequest->status = 'approved';
        $editRequest->save();

        return redirect()->route('admin.requests.index')->with('success', '修正申請を承認しました');
    }
}
