<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in_time',
        'clock_out_time',
        'status',
        'note',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 既存で breaks() を使っているなら残す
    public function breaks(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    // 今回の実装では breakTimes() を使う（with('breakTimes')）
    public function breakTimes(): HasMany
    {
        return $this->hasMany(BreakTime::class);
    }

    // 今回使う修正申請
    public function changeRequests(): HasMany
    {
        return $this->hasMany(\App\Models\AttendanceChangeRequest::class);
    }

    
}

