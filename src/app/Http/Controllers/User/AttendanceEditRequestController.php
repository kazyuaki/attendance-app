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
        $attendanceId =
            $request->input('id') ??
            $request->input('attendance_id') ??  
            $request->query('id') ??
            $request->route('id');
            
        abort_unless($attendanceId, 404);

        $attendance = Attendance::findOrFail($attendanceId);
        abort_if($attendance->user_id !== Auth::id(), 403);

        $validated = $request->validated();

        $date = $attendance->work_date instanceof \Carbon\Carbon
            ? $attendance->work_date->format('Y-m-d')
            : \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d');

        $clock_in = !empty($validated['clock_in'])
            ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$date} {$validated['clock_in']}")
            : null;

        $clock_out = !empty($validated['clock_out'])
            ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$date} {$validated['clock_out']}")
            : null;


        DB::transaction(function () use ($validated, $date, $clock_in, $clock_out, $attendance) {

            $requestModel = AttendanceEditRequest::create([
                'user_id'       => Auth::id(),
                'attendance_id' => $attendance->id,
                'clock_in'      => $clock_in,
                'clock_out'     => $clock_out,
                'note'          => $validated['note'] ?? null,
            ]);

            if (!empty($validated['breaks']) && is_array($validated['breaks'])) {
                foreach ($validated['breaks'] as $break) {
                    $start = !empty($break['start']) ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date {$break['start']}") : null;
                    $end   = !empty($break['end'])   ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "$date {$break['end']}")   : null;

                    if ($start || $end) {
                        $requestModel->editRequestBreaks()->create([
                            'break_start' => $start,
                            'break_end'   => $end,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('attendance.show', ['id' => $attendance->id])
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
