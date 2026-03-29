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
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))
            : now();

        $attendances = Attendance::with(['user', 'breakTimes'])
            ->whereDate('work_date', $date->toDateString())
            ->orderBy('user_id')
            ->get();

        $rows = $attendances->map(function (Attendance $a) {

            $clockIn = $a->clock_in_time
                ? Carbon::parse($a->clock_in_time)->format('H:i')
                : '';

            $clockOut = $a->clock_out_time
                ? Carbon::parse($a->clock_out_time)->format('H:i')
                : '';

            // 休憩合計
            $breakMinutes = $a->breakTimes->sum(function ($b) {
                if (!$b->break_start_time || !$b->break_end_time) {
                    return 0;
                }

                return Carbon::parse($b->break_start_time)
                    ->diffInMinutes(Carbon::parse($b->break_end_time));
            });

            $break = $breakMinutes
                ? sprintf('%d:%02d', floor($breakMinutes / 60), $breakMinutes % 60)
                : '';

            // 勤務合計
            $total = '';

            if ($a->clock_in_time && $a->clock_out_time) {

                $workMinutes = Carbon::parse($a->clock_in_time)
                    ->diffInMinutes(Carbon::parse($a->clock_out_time));

                $workMinutes -= $breakMinutes;

                if ($workMinutes < 0) {
                    $workMinutes = 0;
                }

                $total = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
            }

            return [
                'attendance_id' => $a->id,
                'name' => $a->user?->name ?? '',
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break' => $break,
                'total' => $total,
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
            ? \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
            : now()->startOfMonth();
    } catch (\Exception $e) {
        $currentMonth = now()->startOfMonth();
    }

    $attendances = Attendance::where('user_id', $user->id)
        ->whereBetween('work_date', [
            $currentMonth->copy()->startOfMonth()->toDateString(),
            $currentMonth->copy()->endOfMonth()->toDateString()
        ])
        ->orderBy('work_date')
        ->get();

    return view('admin.attendance.staff_monthly', [
        'user' => $user,
        'currentMonth' => $currentMonth,
        'attendances' => $attendances,
    ]);
}

    public function update(Request $request, $id)
{
    $validated = $request->validate([
        'requested_clock_in_time'  => ['required', 'date_format:H:i'],
        'requested_clock_out_time' => ['required', 'date_format:H:i', 'after:requested_clock_in_time'],
        'requested_note' => ['required'],

        'breaks' => ['nullable', 'array'],
        'breaks.*.start' => ['nullable', 'date_format:H:i', 'before:requested_clock_out_time'],
        'breaks.*.end'   => ['nullable', 'date_format:H:i', 'after:breaks.*.start', 'before:requested_clock_out_time'],
    ], [
        'requested_clock_out_time.after' => '出勤時間もしくは退勤時間が不適切な値です',

        'breaks.*.start.before' => '休憩時間が不適切な値です',
        'breaks.*.end.after'    => '休憩時間が不適切な値です',
        'breaks.*.end.before'   => '休憩時間もしくは退勤時間が不適切な値です',

        'requested_note.required' => '備考を記入してください',
    ]);

    $attendance = Attendance::with('breakTimes')->findOrFail($id);

    $attendance->update([
        'clock_in_time'  => $validated['requested_clock_in_time'],
        'clock_out_time' => $validated['requested_clock_out_time'],
        'note'           => $validated['requested_note'],
    ]);

    $break = $attendance->breakTimes->first();

    $start = $request->input('breaks.0.start');
    $end   = $request->input('breaks.0.end');

    if ($break) {
        $break->update([
            'attendance_id'    => $attendance->id,
            'break_start_time' => $start ? $start . ':00' : null,
            'break_end_time'   => $end ? $end . ':00' : null,
        ]);
    } else {
        $attendance->breakTimes()->create([
            'attendance_id'    => $attendance->id,
            'break_start_time' => $start ? $start . ':00' : null,
            'break_end_time'   => $end ? $end . ':00' : null,
        ]);
    }

    return redirect()->route('admin.attendance.detail', $attendance->id);
}}