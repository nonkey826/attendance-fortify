<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;

class AttendanceCorrectionController extends Controller
{
    public function store(Request $request, Attendance $attendance)
    {
        // 自分の勤怠のみ
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        // すでに承認待ちがある場合は作らない
        $existsPending = AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        if ($existsPending) {
            return redirect()
                ->route('attendance.detail', ['id' => $attendance->id])
                ->withErrors(['request' => 'この勤怠はすでに承認待ちの修正申請があります。']);
        }

        // バリデーション
        $validated = $request->validate([
            'requested_clock_in_time'  => ['required'],
            'requested_clock_out_time' => ['required'],
            'requested_note'           => ['required', 'max:2000'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.start' => ['nullable'],
            'breaks.*.end' => ['nullable'],
        ], [
            'requested_clock_in_time.required'  => '出勤時間を入力してください',
            'requested_clock_out_time.required' => '退勤時間を入力してください',
            'requested_note.required'           => '備考を記入してください',
        ]);

        // 休憩整形
        $breaks = collect($validated['breaks'] ?? [])
    ->map(fn ($b) => [
        'start' => $b['start'] ?? null,
        'end'   => $b['end'] ?? null,
    ])
    ->filter(fn ($b) => $b['start'] || $b['end'])
    ->values()
    ->toJson();

        // 保存
        AttendanceCorrectionRequest::create([
    'attendance_id' => $attendance->id,
    'user_id'       => auth()->id(),

    'requested_work_date'      => $attendance->work_date,
    'requested_clock_in_time'  => $validated['requested_clock_in_time'],
    'requested_clock_out_time' => $validated['requested_clock_out_time'],

    'requested_break_start_time' => $validated['breaks'][0]['start'] ?? null,
    'requested_break_end_time'   => $validated['breaks'][0]['end'] ?? null,

    'requested_note' => $validated['requested_note'],
    'status' => 'pending',
]);

        return redirect()
            ->route('attendance.detail', ['id' => $attendance->id])
            ->with('status', '修正申請を送信しました（承認待ち）');
    }
}