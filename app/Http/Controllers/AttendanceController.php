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
 $now = now();

        $attendance = Attendance::where('user_id', auth()->id())
           ->whereDate('work_date', $today)
            ->first();

        $activeBreak = null;
        $status = '勤務外';

        $status = '勤務外';

if ($attendance) {
    $status = match ($attendance->status) {
        'working'  => '出勤中',
        'break'    => '休憩中',
        'finished' => '退勤済',
        default    => '勤務外',
    };
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
    $month = $request->get('month') ?: now()->format('Y-m');

    $base = \Carbon\Carbon::parse($month . '-01');

    $start = $base->copy()->startOfMonth();
    $end   = $base->copy()->endOfMonth();

    $prev = $base->copy()->subMonth()->format('Y-m');
    $next = $base->copy()->addMonth()->format('Y-m');

    $period = \Carbon\CarbonPeriod::create($start, $end);

    $attendances = Attendance::with('breakTimes')
        ->where('user_id', auth()->id())
        ->whereBetween('work_date', [
            $start->toDateString(),
            $end->toDateString()
        ])
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

            if ($attendance->clock_in_time) {
                $clockIn = \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i');
            }

            if ($attendance->clock_out_time) {
                $clockOut = \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i');
            }

            $breakSeconds = 0;

            foreach ($attendance->breakTimes as $break) {

                if ($break->break_start_time && $break->break_end_time) {

                    $bs = \Carbon\Carbon::parse($break->break_start_time);
                    $be = \Carbon\Carbon::parse($break->break_end_time);

                    $diff = $bs->diffInSeconds($be);

                    if ($diff > 0) {
                        $breakSeconds += $diff;
                    }
                }
            }

            if ($breakSeconds > 0) {

                $bh = floor($breakSeconds / 3600);
                $bm = floor(($breakSeconds % 3600) / 60);

                $breakTotal = sprintf('%d:%02d', $bh, $bm);
            }

            if ($attendance->clock_in_time && $attendance->clock_out_time) {

                $startWork = \Carbon\Carbon::parse($attendance->clock_in_time);
                $endWork   = \Carbon\Carbon::parse($attendance->clock_out_time);

                $workSeconds = $startWork->diffInSeconds($endWork);

                $workSeconds -= $breakSeconds;

                if ($workSeconds < 0) {
                    $workSeconds = 0;
                }

                $wh = floor($workSeconds / 3600);
                $wm = floor(($workSeconds % 3600) / 60);

                $workTotal = sprintf('%d:%02d', $wh, $wm);
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

    return view('attendance.list', compact('data', 'month', 'prev', 'next'));
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
    ->exists() && $attendance->status === 'break';

        if ($isBreaking) {
            return redirect()->route('attendance.index')
                ->with('error', 'すでに休憩中です');
        }

        // BreakTime::create([
        //     'attendance_id'    => $attendance->id,
        //     'break_start_time' => now()->toTimeString(),
        // ]);

        BreakTime::create([
    'attendance_id'    => $attendance->id,
    'break_start_time' => now()->toTimeString(),
]);

$attendance->status = 'break';
$attendance->save();

        
        
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

        $attendance->update([
            'status' => 'working',
        ]);

        return redirect()->route('attendance.index');
    }
}