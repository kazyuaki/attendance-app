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
        $pendingRequest = AttendanceEditRequest::where('attendance_id', $id)
            ->where('status', 'pending')
            ->first();

        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);


        $breaks = $attendance->breakTimes;

        return view('admin.request.approval', compact('attendance',  'pendingRequest'));
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

        $attendance->breakTimes()->delete();

        // editRequestBreaks をもとに breakTimes を再作成
        foreach ($editRequest->editRequestBreaks as $editBreak) {
            $attendance->breakTimes()->create([
                'break_in' => $editBreak->break_start,
                'break_out' => $editBreak->break_end,
            ]);
        }

        // 修正申請ステータス更新
        $editRequest->status = 'approved';
        $editRequest->save();

        return redirect()->route('admin.requests.index')->with('success', '修正申請を承認しました');
    }
}
