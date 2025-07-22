<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;


class AttendanceController extends Controller
{
    public function index()
    {
        $attendances = Attendance::with(['user', 'breakTimes', 'attendanceEditRequests'])->latest()->get();
        return view('admin.attendances.index', compact('attendances'));
    }
}
