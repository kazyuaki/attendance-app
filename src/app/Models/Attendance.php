<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'note',
    ];

    protected $casts = [
        'work_date' => 'date:Y-m-d',
        'clock_in'  => 'datetime',
        'clock_out' => 'datetime',
        'breakTimes.*.break_in'  => 'datetime',
        'breakTimes.*.break_out' => 'datetime',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function attendanceEditRequests()
    {
        return $this->hasMany(AttendanceEditRequest::class);
    }
}
