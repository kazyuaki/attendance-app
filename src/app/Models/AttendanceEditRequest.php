<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceEditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'user_id',
        'clock_in',
        'clock_out',
        'break1_start',
        'break1_end',
        'break2_start',
        'break2_end',
        'note'
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function editRequestBreaks()
    {
        return $this->hasMany(AttendanceEditRequestBreak::class);
    }

    public function breaks()
    {
        return $this->hasMany(AttendanceEditRequestBreak::class);
    }
}
