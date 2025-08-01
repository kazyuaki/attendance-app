<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceEditRequestBreak extends Model
{
    protected $fillable = [
        'attendance_edit_request_id',
        'break_start',
        'break_end',
    ];

    public function attendanceEditRequest()
    {
        return $this->belongsTo(AttendanceEditRequest::class);
    }
}
