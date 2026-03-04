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
        // ※ 勤怠一覧は AttendanceController@list を使っている前提
        // 設計書上 AttendanceListController@index が必要なら存在だけさせる
        return redirect()->route('attendance.list');
    }

    /*
    |--------------------------------------------------------------------------
    | 勤怠詳細画面（一般ユーザー）
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        // 自分の勤怠だけ取得（他人のIDを入れても取れない）
        $attendance = Attendance::with(['user', 'breaks'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // 休憩（0,1で取りやすく）
        $breaks = $attendance->breaks->values();

        // 承認待ち（pending）の修正申請があるか？
        $pendingRequest = AttendanceCorrectionRequest::where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        return view('attendance.detail', compact('attendance', 'breaks', 'pendingRequest'));
    }
}