@extends('layouts.app')

@section('content')
<div class="attendance-list-page">
  <div class="attendance-container">

    <h1 class="page-title">{{ $user->name }} さんの勤怠</h1>

    {{-- 月ナビ --}}
<div class="month-card">

  <a class="month-link"
     href="/admin/attendance/staff/{{ $user->id }}?month={{ \Carbon\Carbon::parse(request('month', now()->format('Y-m')))->subMonth()->format('Y-m') }}">
    <img src="{{ asset('images/arrow.png') }}" class="arrow-left">
    前月
  </a>

  <div class="month-label">
    <img src="{{ asset('images/month.png') }}" class="calendar-icon">
   {{ \Carbon\Carbon::parse(request('month', now()->format('Y-m')))->format('Y/m') }}
  </div>

  <a class="month-link"
     href="/admin/attendance/staff/{{ $user->id }}?month={{ \Carbon\Carbon::parse(request('month', now()->format('Y-m')))->addMonth()->format('Y-m') }}">
    翌月
    <img src="{{ asset('images/arrow.png') }}" class="arrow-right">
  </a>

</div>

    @php
      $attendanceByDate = $attendances->keyBy(fn($a) => \Carbon\Carbon::parse($a->work_date)->toDateString());
      $start = $currentMonth->copy()->startOfMonth();
      $end   = $currentMonth->copy()->endOfMonth();
      $weeks = ['日','月','火','水','木','金','土'];
    @endphp

    <div class="table-card">
      <table class="attendance-table">
        <thead>
          <tr>
            <th>日付</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
          </tr>
        </thead>

        <tbody>
          @for ($d = $start->copy(); $d->lte($end); $d->addDay())
            @php
              $key = $d->toDateString();
              $attendance = $attendanceByDate->get($key);
            @endphp

            <tr>

              <td>
                {{ $d->format('m/d') }}({{ $weeks[$d->dayOfWeek] }})
              </td>

              {{-- 出勤 --}}
              <td>
                {{ $attendance && $attendance->clock_in_time
                    ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i')
                    : '' }}
              </td>

              {{-- 退勤 --}}
              <td>
                {{ $attendance && $attendance->clock_out_time
                    ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i')
                    : '' }}
              </td>

              {{-- 休憩 --}}
              <td>
                @if($attendance)
                  @php
                    $breakMinutes = $attendance->breakTimes->sum(function ($b) {
                        if (!$b->break_start_time || !$b->break_end_time) return 0;

                        return \Carbon\Carbon::parse($b->break_start_time)
                            ->diffInMinutes(\Carbon\Carbon::parse($b->break_end_time));
                    });
                  @endphp

                  @if($breakMinutes > 0)
                    {{ floor($breakMinutes / 60) }}:{{ str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) }}
                  @endif
                @endif
              </td>

              {{-- 合計勤務 --}}
              <td>
                @if($attendance && $attendance->clock_in_time && $attendance->clock_out_time)

                  @php
                    $workMinutes = \Carbon\Carbon::parse($attendance->clock_in_time)
                        ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out_time));

                    $breakMinutes = $attendance->breakTimes->sum(function ($b) {
                        if (!$b->break_start_time || !$b->break_end_time) return 0;

                        return \Carbon\Carbon::parse($b->break_start_time)
                            ->diffInMinutes(\Carbon\Carbon::parse($b->break_end_time));
                    });

                    $workMinutes -= $breakMinutes;

                    if ($workMinutes < 0) {
                        $workMinutes = 0;
                    }
                  @endphp

                  {{ floor($workMinutes / 60) }}:{{ str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) }}

                @endif
              </td>

              {{-- 詳細 --}}
              <td>
                @if($attendance)
                  <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                @endif
              </td>

            </tr>

          @endfor
        </tbody>
      </table>
    </div>

  </div>
</div>
@endsection