<?php

namespace App\Http\Controllers\User;

use App\Http\Requests\AttendanceEditRequestForm;
use App\Http\Controllers\Controller;
use App\Models\AttendanceEditRequest;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceEditRequestController extends Controller
{
    public function store(AttendanceEditRequestForm $request)
    {
        $validated = $request->validated();        
        $attendance = Attendance::findOrFail($validated['attendance_id']);
        $date = $attendance->work_date instanceof \Carbon\Carbon
            ? $attendance->work_date->format('Y-m-d')
            : \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d');

        // 出退勤の時刻を datetime に変換（nullの場合も考慮）
        $clock_in = !empty($validated['clock_in'])
            ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$date} {$validated['clock_in']}")
            : null;

        $clock_out = !empty($validated['clock_out'])
            ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$date} {$validated['clock_out']}")
            : null;

        DB::transaction(function () use ($validated, $date, $clock_in, $clock_out) {

            // 勤怠修正申請レコードの作成
            $requestModel = AttendanceEditRequest::create([
                'user_id' => Auth::id(),
                'attendance_id' => $validated['attendance_id'],
                'clock_in' => $clock_in,
                'clock_out' => $clock_out,
                'note' => $validated['note'] ?? null,
            ]);

            // 休憩情報（breaks）を保存（複数対応）
            if (!empty($validated['breaks']) && is_array($validated['breaks'])) {
                foreach ($validated['breaks'] as $break) {
                    $start = !empty($break['start']) ? Carbon::createFromFormat('Y-m-d H:i', "$date {$break['start']}") : null;
                    $end   = !empty($break['end'])   ? Carbon::createFromFormat('Y-m-d H:i', "$date {$break['end']}") : null;

                    // どちらか一方でもあれば保存（完全に空の行はスキップ）
                    if ($start || $end) {
                        $requestModel->editRequestBreaks()->create([
                            'break_start' => $start,
                            'break_end' => $end,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('attendance.show', ['id' => $validated['attendance_id']])
            ->with('success', '修正申請を送信しました。');
    }

    public function index(Request $request)
    {
        $status = $request->input('status','pending');
        $requests = AttendanceEditRequest::with(['attendance', 'user', 'editRequestBreaks'])
            ->where('user_id', Auth::id())
            ->where('status', $status)
            ->orderBy('created_at', 'asc')
            ->get();

        return view('user.request.index', compact('requests','status'));
    }


}
