<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrectionRequest extends Model
{
    protected $fillable = [
    'attendance_id',
    'user_id',
    'requested_work_date',
    'requested_clock_in_time',
    'requested_clock_out_time',
    'requested_break_start_time',
    'requested_break_end_time',
    'requested_note',
    'status',
];

    /**
     * 対象の勤怠
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 申請者
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
