<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceEditRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $requests = AttendanceEditRequest::with('user', 'attendance')
            ->when($status === 'done', fn($query) => $query->where('status', 'approved'))
            ->when($status === 'pending', fn($query) => $query->where('status', 'pending'))
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.request.index', compact('requests', 'status'));
    }


    public function show($id)
    {
        $pendingRequest = AttendanceEditRequest::where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $attendance = Attendance::with('user', 'breakTimes')
            ->findOrFail($pendingRequest->attendance_id);


        $breaks = $attendance->breakTimes->groupBy('break_number');
        $break1 = $breaks->get(1) ? $breaks->get(1)->first() : null;
        $break2 = $breaks->get(2) ? $breaks->get(2)->first() : null;

        return view('admin.request.approval', compact('attendance', 'break1', 'break2', 'pendingRequest'));
    }



    // 承認処理（管理者側）
    public function approve(Request $request, $id)
    {
        $editRequest = AttendanceEditRequest::where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        // 出勤・退勤・備考の更新
        $attendance = $editRequest->attendance;
        $attendance->clock_in = $editRequest->clock_in;
        $attendance->clock_out = $editRequest->clock_out;
        $attendance->note = $editRequest->note;
        $attendance->save();

        // 休憩1を更新
        $break1 = $attendance->breakTimes()->where('break_number', 1)->first();
        if ($break1 && $editRequest->break1_start && $editRequest->break1_end) {
            $break1->break_start = $editRequest->break1_start;
            $break1->break_end = $editRequest->break1_end;
            $break1->save();
        }

        // 休憩2を更新（必要なら）
        $break2 = $attendance->breakTimes()->where('break_number', 2)->first();
        if ($break2 && $editRequest->break2_start && $editRequest->break2_end) {
            $break2->break_start = $editRequest->break2_start;
            $break2->break_end = $editRequest->break2_end;
            $break2->save();
        }

        // 修正申請ステータス更新
        $editRequest->status = 'approved';
        $editRequest->save();

        return redirect()->route('admin.requests.index')->with('success', '修正申請を承認しました');
    }
}
