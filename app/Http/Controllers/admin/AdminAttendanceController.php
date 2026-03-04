<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        // ?date=YYYY-MM-DD があればその日、なければ今日
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : now();

        // 指定日の勤怠を取得（ユーザー名表示のため user を eager load）
        $attendances = Attendance::with('user')
            ->whereDate('work_date', $date->toDateString())
            ->orderBy('user_id')
            ->get();

        // Bladeで使う表示用配列に整形
        $rows = $attendances->map(function (Attendance $a) {
            $clockIn = $a->clock_in_time
                ? Carbon::parse($a->clock_in_time)->format('H:i')
                : '';

            $clockOut = $a->clock_out_time
                ? Carbon::parse($a->clock_out_time)->format('H:i')
                : '';

            return [
                'attendance_id' => $a->id,
                'name'          => $a->user?->name ?? '',
                'clock_in'      => $clockIn,
                'clock_out'     => $clockOut, // ← 退勤はここ
                'break'         => '',        // 休憩はまだ未実装（UIだけ先に合わせる）
                'total'         => '',        // 合計もまだ未実装
            ];
        })->values();

        return view('admin.attendance.list', [
    'date' => $date,   // ← Carbonのまま渡す
    'rows' => $rows,
]);
    }

    public function show($id)
    {
        // まずは存在確認だけ（詳細は次ステップで実装）
        $attendance = Attendance::with('user')->findOrFail($id);

        return "admin attendance detail {$attendance->id}";
    }


public function staffMonthly(Request $request, $user)
{
    // まずは遷移確認用（設計書外の集計等はまだしない）
    return "admin staff monthly attendance user_id={$user}";
}



}