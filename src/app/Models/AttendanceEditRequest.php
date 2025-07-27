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
}
