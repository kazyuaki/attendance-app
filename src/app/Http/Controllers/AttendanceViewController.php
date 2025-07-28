<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceViewController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakTimes', 'break1', 'break2')->findOrFail($id);

        $pendingRequest = AttendanceEditRequest::where('user_id', Auth::id())
            ->where('attendance_id', $id)
            ->where('status', 'pending')
            ->first();

        $breaks = $attendance->breakTimes->groupBy('break_number');
        $break1 = $breaks->get(1) ? $breaks->get(1)->first() : null;
        $break2 = $breaks->get(2) ? $breaks->get(2)->first() : null;

        return view('user.attendance.show', compact('attendance', 'break1', 'break2', 'pendingRequest'));
    }
}
