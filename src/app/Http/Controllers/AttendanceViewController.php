<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;



class AttendanceViewController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breakTimes','break1','break2')->findOrFail($id);

        $breaks = $attendance->breakTimes->groupBy('break_number');
        $break1 = $breaks->get(1) ? $breaks->get(1)->first() : null;
        $break2 = $breaks->get(2) ? $breaks->get(2)->first() : null;

        return view('user.attendance.show', compact('attendance', 'break1', 'break2'));
    }
}
