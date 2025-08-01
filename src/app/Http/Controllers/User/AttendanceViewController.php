<?php

namespace App\Http\Controllers\User;

use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AttendanceViewController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

        $pendingRequest = AttendanceEditRequest::where('user_id', Auth::id())
            ->where('attendance_id', $id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $breakTimes = $attendance->breakTimes->sortBy('break_in');

        return view('user.attendance.show', compact('attendance',  'pendingRequest', 'breakTimes'));
    }
}
