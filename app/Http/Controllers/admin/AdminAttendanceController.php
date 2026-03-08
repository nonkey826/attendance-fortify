<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAttendanceController extends Controller
{
    /**
     * 管理者 勤怠一覧
     */
    public function index(Request $request)
    {
        // ?date=YYYY-MM-DD があればその日、なければ今日
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : now();

        // 指定日の勤怠取得
        $attendances = Attendance::with('user')
            ->whereDate('work_date', $date->toDateString())
            ->orderBy('user_id')
            ->get();

        // Blade用データ整形
        $rows = $attendances->map(function (Attendance $a) {

            $clockIn = $a->clock_in_time
                ? Carbon::parse($a->clock_in_time)->format('H:i')
                : '';

            $clockOut = $a->clock_out_time
                ? Carbon::parse($a->clock_out_time)->format('H:i')
                : '';

            return [
                'attendance_id' => $a->id,
                'name' => $a->user?->name ?? '',
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break' => '',
                'total' => '',
            ];
        })->values();

        return view('admin.attendance.list', [
            'date' => $date,
            'rows' => $rows,
        ]);
    }

    /**
     * 管理者 勤怠詳細
     */
    public function show($id)
    {
        $attendance = Attendance::with(['user', 'breakTimes'])
            ->findOrFail($id);

        return view('admin.attendance.detail', [
            'attendance' => $attendance
        ]);
    }

    /**
     * スタッフ別 月次勤怠
     */
    public function staffMonthly(Request $request, User $user)
    {
        $monthParam = $request->query('month');

        try {
            $currentMonth = $monthParam
                ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
                : now()->startOfMonth();
        } catch (\Exception $e) {
            $currentMonth = now()->startOfMonth();
        }

        $year = (int) $currentMonth->year;
        $month = (int) $currentMonth->month;

        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('work_date', $month)
            ->whereYear('work_date', $year)
            ->orderBy('work_date')
            ->get();

        return view('admin.attendance.staff_monthly', [
            'user' => $user,
            'currentMonth' => $currentMonth,
            'attendances' => $attendances,
        ]);
    }

    /**
     * 管理者 勤怠更新（PG09）
     */
    public function update(Request $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        // 勤怠更新
        $attendance->update([
            'clock_in_time' => $request->clock_in_time,
            'clock_out_time' => $request->clock_out_time,
            'note' => $request->note,
        ]);

        // 休憩更新（1件想定）
        if ($attendance->breakTimes->first()) {

            $break = $attendance->breakTimes->first();

            $break->update([
                'break_start_time' => $request->break_start_time,
                'break_end_time' => $request->break_end_time,
            ]);
        }

        return redirect()->route('admin.attendance.detail', $attendance->id);
    }
}