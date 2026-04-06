<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


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
//

$validated = $request->validate([
    'requested_clock_in_time'  => ['required', 'date_format:H:i', 'before:requested_clock_out_time'],
    'requested_clock_out_time' => ['required', 'date_format:H:i', 'after:requested_clock_in_time'],
    'requested_note' => ['required', 'filled'],

    'breaks' => ['nullable', 'array'],
    'breaks.*.start' => ['nullable', 'date_format:H:i', 'before:requested_clock_out_time'],
    'breaks.*.end'   => ['nullable', 'date_format:H:i', 'after:breaks.*.start', 'before:requested_clock_out_time'],
], [
    'requested_clock_in_time.before' => '出勤時間が不適切な値です。',
    'requested_clock_out_time.after' => '退勤時間が不適切な値です。',
    'breaks.*.start.before' => '休憩時間が不適切な値です。',
    'breaks.*.end.after'    => '休憩時間もしくは退勤時間が不適切な値です。',
    'breaks.*.end.before'   => '休憩時間もしくは退勤時間が不適切な値です。',
    'requested_note.required' => '備考を記入してください。',
    'requested_clock_in_time.date_format' => '出勤時間はH:iの形式で入力してください。',
    'requested_clock_out_time.date_format' => '退勤時間はH:iの形式で入力してください。',
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


'requested_break_start_time' => json_encode(array_column($request->input('breaks', []), 'start')),
'requested_break_end_time'   => json_encode(array_column($request->input('breaks', []), 'end')),

    'requested_note' => $validated['requested_note'],
    'status' => 'pending',
]);

        return redirect()
            ->route('attendance.detail', ['id' => $attendance->id])
            ->with('status', '修正申請を送信しました（承認待ち）');
    }
}