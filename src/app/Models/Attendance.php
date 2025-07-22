<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'word_date',
        'status',
        'check_in' ,
        'check_out',
        'note',
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
