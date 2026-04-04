<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceCorrectionRequest;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Models\BreakTime;
use Illuminate\Http\Request;

class AttendanceCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $query = AttendanceCorrectionRequest::with(['user', 'attendance']);

        if (auth()->user()->role !== 'admin') {
            $query->where('user_id', auth()->id());
        }

        $query->where('status', $status);

        $requests = $query->orderByDesc('created_at')->get();

        return view('stamp_correction_request.list', [
            'status' => $status,
            'requests' => $requests
        ]);
    }

    public function store(StoreAttendanceCorrectionRequest $request, Attendance $attendance)
    {
        if ($attendance->user_id !== auth()->id()) {
            abort(403);
        }

        $existsPending = AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->exists();

        if ($existsPending) {
            return back()
                ->withErrors(['request' => '承認待ちのため修正はできません。'])
                ->withInput();
        }

        $validated = $request->validated();

        $breaks = collect($request->input('breaks', []))
    ->filter(function ($b) {
        return !empty($b['start']) && !empty($b['end']);
    })
    ->values();

        AttendanceCorrectionRequest::create([
            'attendance_id'              => $attendance->id,
            'user_id'                    => auth()->id(),
            'requested_work_date'        => $validated['requested_work_date'] ?? null,
            'requested_clock_in_time'    => $validated['requested_clock_in_time'] ?? null,
            'requested_clock_out_time'   => $validated['requested_clock_out_time'] ?? null,

            'requested_break_start_time' => json_encode($breaks->pluck('start')->toArray()),
'requested_break_end_time'   => json_encode($breaks->pluck('end')->toArray()),

            'requested_note'             => $validated['requested_note'] ?? null,
            'status'                     => 'pending',
        ]);

        return redirect()
            ->route('attendance.detail', $attendance->id)
            ->with('status', '修正申請を送信しました（承認待ち）');
    }

    public function approve($attendance_correct_request_id)
    {
        $correctionRequest = AttendanceCorrectionRequest::with('attendance.breakTimes')
            ->findOrFail($attendance_correct_request_id);

        $attendance = $correctionRequest->attendance;

        if (!$attendance) {
            return redirect()
                ->route('stamp_correction_request.list', ['status' => 'pending'])
                ->withErrors(['request' => '対象の勤怠情報が見つかりません。']);
        }

        if ($correctionRequest->requested_work_date) {
            $attendance->work_date = $correctionRequest->requested_work_date;
        }

        if ($correctionRequest->requested_clock_in_time) {
            $attendance->clock_in_time = $correctionRequest->requested_clock_in_time;
        }

        if ($correctionRequest->requested_clock_out_time) {
            $attendance->clock_out_time = $correctionRequest->requested_clock_out_time;
        }

        if ($correctionRequest->requested_note) {
            $attendance->note = $correctionRequest->requested_note;
        }

        $attendance->save();

        $attendance->breakTimes()->delete();

$starts = json_decode($correctionRequest->requested_break_start_time, true) ?? [];
$ends   = json_decode($correctionRequest->requested_break_end_time, true) ?? [];

foreach ($starts as $i => $start) {
    $end = $ends[$i] ?? null;

    if ($start || $end) {
        $attendance->breakTimes()->create([
            'attendance_id'    => $attendance->id,
            'break_start_time' => $start ? $start . ':00' : null,
            'break_end_time'   => $end ? $end . ':00' : null,
        ]);
    }
}

        $correctionRequest->status = 'approved';
        $correctionRequest->save();

        return redirect()
            ->route('admin.stamp_correction_request.detail', $correctionRequest->id)
            ->with('status', '承認しました');
    }

    public function show($id)
    {
        $correctionRequest = AttendanceCorrectionRequest::with(['user', 'attendance.breakTimes'])
            ->findOrFail($id);

        $attendance = $correctionRequest->attendance;
        $breaks = $attendance->breakTimes;

        return view('stamp_correction_request.show', compact('correctionRequest', 'attendance', 'breaks'));
    }

    public function adminShow($id)
    {
        $correctionRequest = AttendanceCorrectionRequest::with(['user', 'attendance.breakTimes'])
            ->findOrFail($id);

        $attendance = $correctionRequest->attendance;
        $breaks = $attendance->breakTimes;

        return view('stamp_correction_request.show', compact('correctionRequest', 'attendance', 'breaks'));
    }
}