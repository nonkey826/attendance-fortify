@extends('layouts.app')

@section('content')
<div class="attendance-list-page">
  <div class="attendance-container">

    <h1 class="page-title">{{ $user->name }} さんの勤怠</h1>

    {{-- 月ナビ --}}
<div class="month-card">

  <a class="month-link"
     href="{{ route('admin.attendance.staff.monthly', [
        'user' => $user->id,
        'month' => $currentMonth->copy()->subMonth()->format('Y-m')
     ]) }}">
    <img src="{{ asset('images/arrow.png') }}" class="arrow-left">
    前月
  </a>

  <div class="month-label">
    <img src="{{ asset('images/month.png') }}" class="calendar-icon">
    {{ $currentMonth->format('Y/m') }}
  </div>

  <a class="month-link"
     href="{{ route('admin.attendance.staff.monthly', [
        'user' => $user->id,
        'month' => $currentMonth->copy()->addMonth()->format('Y-m')
     ]) }}">
    翌月
    <img src="{{ asset('images/arrow.png') }}" class="arrow-right">
  </a>

</div>

    @php
      $attendanceByDate = $attendances->keyBy(fn($a) => \Carbon\Carbon::parse($a->work_date)->toDateString());
      $start = $currentMonth->copy()->startOfMonth();
      $end   = $currentMonth->copy()->endOfMonth();
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
              <td>{{ $d->format('m/d') }}({{ $d->locale('ja')->isoFormat('ddd') }})</td>
              <td>{{ $attendance && $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '' }}</td>
              <td>{{ $attendance && $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '' }}</td>
              <td></td>
              <td></td>
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

    {{-- CSV出力ボタンは要件で作らない --}}

  </div>
</div>
@endsection