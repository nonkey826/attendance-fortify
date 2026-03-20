<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;

class AttendanceListController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 勤怠一覧画面（設計書に index がある場合のダミー）
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        return redirect()->route('attendance.list');
    }

    /*
    |--------------------------------------------------------------------------
    | 勤怠詳細画面（一般ユーザー）
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $breaks = $attendance->breakTimes->values();

        $pendingRequest = AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

     
     
        
     
     
        return view('attendance.detail', compact('attendance', 'breaks', 'pendingRequest'));
    }
}