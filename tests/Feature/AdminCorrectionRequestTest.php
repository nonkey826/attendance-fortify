<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceCorrectionRequest;
use App\Models\Attendance;

class AttendanceCorrectionRequestController extends Controller
{
    public function adminIndex()
    {
        $requests = AttendanceCorrectionRequest::with(['user', 'attendance'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.stamp_correction_request.list', compact('requests'));
    }

    public function detail($id)
    {
        $requestData = AttendanceCorrectionRequest::with(['user', 'attendance'])
            ->findOrFail($id);

        return view('admin.stamp_correction_request.detail', compact('requestData'));
    }

    public function approve($id)
    {
        $requestData = AttendanceCorrectionRequest::findOrFail($id);

        $attendance = Attendance::findOrFail($requestData->attendance_id);

        // 勤怠更新
        $attendance->update([
            'clock_in_time'  => $requestData->requested_clock_in_time,
            'clock_out_time' => $requestData->requested_clock_out_time,
            'note'           => $requestData->requested_note,
        ]);

        // 申請ステータス更新
        $requestData->update([
            'status' => 'approved',
        ]);

        return redirect()->route('admin.stamp_correction_request.list');
    }
}