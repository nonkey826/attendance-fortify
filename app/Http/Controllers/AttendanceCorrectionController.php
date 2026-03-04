<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceChangeRequest;
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
        $existsPending = AttendanceChangeRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        if ($existsPending) {
            return redirect()
                ->route('attendance.detail', ['id' => $attendance->id])
                ->withErrors(['request' => 'この勤怠はすでに承認待ちの修正申請があります。']);
        }

        // まずは「保存できる」最小バリデーション（備考必須は要件通り）
        $validated = $request->validate([
            'requested_clock_in_time'  => ['required'],
            'requested_clock_out_time' => ['required'],
            'requested_note'           => ['required', 'max:2000'],

            // 休憩（配列で受け取る：breaks[0][start] 等）
            'breaks' => ['nullable', 'array'],
            'breaks.*.start' => ['nullable'],
            'breaks.*.end' => ['nullable'],
        ], [
            'requested_clock_in_time.required'  => '出勤時間を入力してください',
            'requested_clock_out_time.required' => '退勤時間を入力してください',
            'requested_note.required'           => '備考を記入してください',
        ]);

        // 休憩をJSON保存用に整形（空行は除外）
        $breaks = collect($validated['breaks'] ?? [])
            ->map(fn ($b) => [
                'start' => $b['start'] ?? null,
                'end'   => $b['end'] ?? null,
            ])
            ->filter(fn ($b) => !empty($b['start']) || !empty($b['end']))
            ->values()
            ->all();

        AttendanceChangeRequest::create([
            'attendance_id' => $attendance->id,
            'user_id'       => auth()->id(),

            'requested_work_date' => $attendance->work_date, // 今は画面に日付入力がないので現値で埋める
            'requested_clock_in_time'  => $validated['requested_clock_in_time'],
            'requested_clock_out_time' => $validated['requested_clock_out_time'],
            'requested_breaks' => $breaks ?: null,
            'requested_note'   => $validated['requested_note'],

            'status' => 'pending',
        ]);

        return redirect()
            ->route('attendance.detail', ['id' => $attendance->id])
            ->with('status', '修正申請を送信しました（承認待ち）');
    }
}