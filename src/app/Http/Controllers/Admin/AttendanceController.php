<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;


class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with(['user', 'breakTimes', 'attendanceEditRequests'])->latest()->get();
        return view('admin.attendance.index', compact('attendances'));
    }
}
