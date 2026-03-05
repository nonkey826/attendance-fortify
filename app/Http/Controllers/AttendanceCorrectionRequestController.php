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
    $status = $request->query('status', 'pending');

    $query = AttendanceCorrectionRequest::query();

    if ($status === 'approved') {
        $query->where('status', 'approved');
    } else {
        $query->where('status', 'pending');
        $status = 'pending';
    }

    $requests = $query->orderByDesc('created_at')->get();

    return view('stamp_correction_request.list', [
        'status' => $status,
        'requests' => $requests,
    ]);
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

    // 追加：日付
    'requested_work_date'      => $validated['requested_work_date'] ?? null,

    // 既存：出勤・退勤
    'requested_clock_in_time'  => $validated['requested_clock_in_time'] ?? null,
    'requested_clock_out_time' => $validated['requested_clock_out_time'] ?? null,

    // 追加：休憩
    'requested_break_start_time' => $validated['requested_break_start_time'] ?? null,
    'requested_break_end_time'   => $validated['requested_break_end_time'] ?? null,

    // 既存：備考
    'requested_note'           => $validated['requested_note'] ?? null,

    'status'                   => 'pending',
]);

        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('status', '修正申請を送信しました（承認待ち）');
    }

    public function show($id)
{
    $request = AttendanceCorrectionRequest::with(['user','attendance'])
        ->findOrFail($id);

    return view('stamp_correction_request.show', compact('request'));
}

public function approve($attendance_correct_request_id)
{
    $req = AttendanceCorrectionRequest::findOrFail($attendance_correct_request_id);

    $req->update(['status' => 'approved']);

    return redirect()
        ->route('stamp_correction_request.show', $req->id)
        ->with('status', '承認しました（※勤怠反映は未実装）');
}

}