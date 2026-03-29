<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BreakTime extends Model
{
    protected $table = 'breaks';

    protected $fillable = [
    'attendance_id',
    'user_id',
    'break_start_time',
    'break_end_time',
];

    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }
}

