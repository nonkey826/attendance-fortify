<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 打刻画面表示
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', $today)
            ->first();

        $activeBreak = null;

        if ($attendance) {
            $activeBreak = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end_time')
                ->first();
        }

        return view('attendance.index', [
            'attendance' => $attendance,
            'activeBreak' => $activeBreak
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 出勤
    |--------------------------------------------------------------------------
    */
    public function clockIn()
    {
        $today = now()->toDateString();

        $exists = Attendance::where('user_id', auth()->id())
            ->where('work_date', $today)
            ->exists();

        if ($exists) {
            return redirect('/attendance')
                ->with('error', 'すでに出勤しています');
        }

        Attendance::create([
            'user_id' => auth()->id(),
            'work_date' => $today,
            'clock_in_time' => now()->toTimeString(),
            'status' => 'working',
        ]);

        return redirect('/attendance')
            ->with('success', '出勤しました');
    }

    /*
    |--------------------------------------------------------------------------
    | 退勤
    |--------------------------------------------------------------------------
    */
    public function clockOut()
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', $today)
            ->first();

        if (!$attendance) {
            return redirect('/attendance')
                ->with('error', '出勤していません');
        }

        if ($attendance->clock_out_time) {
            return redirect('/attendance')
                ->with('error', 'すでに退勤済みです');
        }

        $attendance->update([
            'clock_out_time' => now()->toTimeString(),
            'status' => 'finished',
        ]);

        return redirect('/attendance')
            ->with('success', '退勤しました');
    }

    /*
    |--------------------------------------------------------------------------
    | 勤怠一覧（月表示）
    |--------------------------------------------------------------------------
    */
    public function list(Request $request)
    {
        $month = $request->query('month', now()->format('Y-m'));

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = Carbon::parse($month . '-01')->endOfMonth();

        $period = CarbonPeriod::create($start, $end);

        $attendances = Attendance::where('user_id', auth()->id())
            ->whereBetween('work_date', [$start, $end])
            ->get()
            ->keyBy('work_date');

        $data = [];

        foreach ($period as $date) {
            $dateString = $date->toDateString();

            $attendance = $attendances[$dateString] ?? null;

            $data[] = [
                'date' => $date,
                'attendance' => $attendance,
            ];
        }

        return view('attendance.list', [
            'data' => $data,
            'month' => $month
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | 休憩開始
    |--------------------------------------------------------------------------
    */
    public function breakStart()
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', $today)
            ->first();

        if (!$attendance) {
            return redirect('/attendance')
                ->with('error', '出勤していません');
        }

        if ($attendance->clock_out_time) {
            return redirect('/attendance')
                ->with('error', 'すでに退勤済みです');
        }

        $openBreak = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end_time')
            ->exists();

        if ($openBreak) {
            return redirect('/attendance')
                ->with('error', 'すでに休憩中です');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start_time' => now()->toTimeString(),
        ]);

        return redirect('/attendance')
            ->with('success', '休憩を開始しました');
    }

    /*
    |--------------------------------------------------------------------------
    | 休憩終了
    |--------------------------------------------------------------------------
    */
    public function breakEnd()
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', $today)
            ->first();

        if (!$attendance) {
            return redirect('/attendance')
                ->with('error', '出勤していません');
        }

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end_time')
            ->latest()
            ->first();

        if (!$break) {
            return redirect('/attendance')
                ->with('error', 'アクティブな休憩がありません');
        }

        $break->update([
            'break_end_time' => now()->toTimeString(),
        ]);

        return redirect('/attendance')
            ->with('success', '休憩を終了しました');
    }
}
