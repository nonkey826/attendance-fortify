<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;


class AttendanceCorrectionRequestController extends Controller
{

public function index(Request $request)
    {
        $requests = AttendanceCorrectionRequest::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('stamp_correction_request.list', compact('requests'));
    }






    public function store(StoreAttendanceCorrectionRequest $request, Attendance $attendance)
    {
        // 本人のみ
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        // 既に承認待ちがある場合は弾く
        $existsPending = AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        if ($existsPending) {
            return back()
                ->withErrors(['request' => '承認待ちのため修正はできません。'])
                ->withInput();
        }

        // FormRequestでバリデーション済み
        $validated = $request->validated();

        // 休憩データ整形（空は落とす）
        $breaks = collect($validated['breaks'] ?? [])
            ->map(fn ($b) => [
                'start' => $b['start'] ?? null,
                'end'   => $b['end'] ?? null,
            ])
            ->filter(fn ($b) => !empty($b['start']) || !empty($b['end']))
            ->values()
            ->all();

        AttendanceCorrectionRequest::create([
            'attendance_id'            => $attendance->id,
            'user_id'                  => auth()->id(),
            'requested_work_date'      => $validated['requested_work_date'] ?? null,
            'requested_clock_in_time'  => $validated['requested_clock_in_time'] ?? null,
            'requested_clock_out_time' => $validated['requested_clock_out_time'] ?? null,
            'requested_breaks'         => $breaks ?: null,
            'requested_note'           => $validated['requested_note'],
            'status'                   => 'pending',
        ]);

        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('status', '修正申請を送信しました（承認待ち）');
    }
}