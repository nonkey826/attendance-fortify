<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 打刻画面表示
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        Carbon::setLocale('ja');

        $today = now()->toDateString();
        $now   = now();

        $attendance = Attendance::where('user_id', auth()->id())
            ->where('work_date', $today)
            ->first();

        $activeBreak = null;
        $status = '勤務外';

        if ($attendance) {
            $activeBreak = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end_time')
                ->first();

            if ($attendance->clock_out_time) {
                $status = '退勤済';
            } elseif ($activeBreak) {
                $status = '休憩中';
            } else {
                $status = '出勤中';
            }
        }

        return view('attendance.index', [
            'attendance'  => $attendance,
            'activeBreak' => $activeBreak,
            'status'      => $status,
            'now'         => $now,
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
            return redirect()->route('attendance.index')
                ->with('error', 'すでに出勤しています');
        }

        Attendance::create([
            'user_id'       => auth()->id(),
            'work_date'     => $today,
            'clock_in_time' => now()->toTimeString(),
            'status'        => 'working',
        ]);

        return redirect()->route('attendance.index');
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
            return redirect()->route('attendance.index')
                ->with('error', '出勤していません');
        }

        if ($attendance->clock_out_time) {
            return redirect()->route('attendance.index')
                ->with('error', 'すでに退勤済みです');
        }

        $attendance->update([
            'clock_out_time' => now()->toTimeString(),
            'status'         => 'finished',
        ]);

        return redirect()->route('attendance.index');
    }

    /*
    |--------------------------------------------------------------------------
    | 勤怠一覧（月表示）
    |--------------------------------------------------------------------------
    */
    public function list(Request $request)
    {
        // Carbon::setLocale('ja');

        $month = $request->query('month', now()->format('Y-m'));

        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end   = Carbon::parse($month . '-01')->endOfMonth();

        $period = CarbonPeriod::create($start, $end);

        // work_date は date 型なので toDateString で揃える
        $attendances = Attendance::with('breaks')
            ->where('user_id', auth()->id())
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $data = [];

        foreach ($period as $date) {
            $dateString = $date->toDateString();
            $attendance = $attendances->firstWhere('work_date', $dateString);

            $clockIn    = '';
            $clockOut   = '';
            $breakTotal = '';
            $workTotal  = '';

            if ($attendance) {
                // 秒なし表示
                if ($attendance->clock_in_time) {
                    $clockIn = Carbon::parse($attendance->clock_in_time)->format('H:i');
                }
                if ($attendance->clock_out_time) {
                    $clockOut = Carbon::parse($attendance->clock_out_time)->format('H:i');
                }

                // 休憩合計（秒）
                $breakSeconds = 0;
                foreach ($attendance->breaks as $break) {
                    if ($break->break_start_time && $break->break_end_time) {
                        $bs = Carbon::parse($break->break_start_time);
                        $be = Carbon::parse($break->break_end_time);
                        $diff = $bs->diffInSeconds($be, false);
                        if ($diff > 0) {
                            $breakSeconds += $diff;
                        }
                    }
                }

                if ($breakSeconds > 0) {
                    $bh = floor($breakSeconds / 3600);
                    $bm = floor(($breakSeconds % 3600) / 60);
                    $breakTotal = $bh . ':' . str_pad($bm, 2, '0', STR_PAD_LEFT);
                }

                // 勤務合計（出勤〜退勤 - 休憩）
                if ($attendance->clock_in_time && $attendance->clock_out_time) {
                    $startWork = Carbon::parse($attendance->clock_in_time);
                    $endWork   = Carbon::parse($attendance->clock_out_time);

                    
                    $workSeconds = $startWork->diffInSeconds($endWork, false);

                    if ($breakSeconds > 0) {
                        $workSeconds -= $breakSeconds;
                    }

                    if ($workSeconds < 0) {
                        $workSeconds = 0;
                    }

                    $wh = floor($workSeconds / 3600);
                    $wm = floor(($workSeconds % 3600) / 60);

                    $workTotal = $wh . ':' . str_pad($wm, 2, '0', STR_PAD_LEFT);
                }
            }

            $data[] = [
                'date'       => $date,
                'attendance' => $attendance,
                'clock_in'   => $clockIn,
                'clock_out'  => $clockOut,
                'break'      => $breakTotal,
                'total'      => $workTotal,
            ];
        }

        return view('attendance.list', compact('data', 'month'));
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
            return redirect()->route('attendance.index')
                ->with('error', '出勤していません');
        }

        if ($attendance->clock_out_time) {
            return redirect()->route('attendance.index')
                ->with('error', 'すでに退勤済みです');
        }

        $isBreaking = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end_time')
            ->exists();

        if ($isBreaking) {
            return redirect()->route('attendance.index')
                ->with('error', 'すでに休憩中です');
        }

        BreakTime::create([
            'attendance_id'    => $attendance->id,
            'break_start_time' => now()->toTimeString(),
        ]);

        return redirect()->route('attendance.index');
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
            return redirect()->route('attendance.index')
                ->with('error', '出勤していません');
        }

        if ($attendance->clock_out_time) {
            return redirect()->route('attendance.index')
                ->with('error', 'すでに退勤済みです');
        }

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end_time')
            ->latest()
            ->first();

        if (!$break) {
            return redirect()->route('attendance.index')
                ->with('error', 'アクティブな休憩がありません');
        }

        $break->update([
            'break_end_time' => now()->toTimeString(),
        ]);

        return redirect()->route('attendance.index');
    }
}