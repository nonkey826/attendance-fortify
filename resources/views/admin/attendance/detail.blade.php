@extends('layouts.app')

@section('content')
<div class="attendance-detail-page">
    <div class="attendance-container">

        <h2 class="page-title">勤怠詳細</h2>

        <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
            @csrf

            @if ($errors->any())
                <div class="error-messages">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            @php
                $break1 = $attendance->breakTimes->get(0);
                $break2 = $attendance->breakTimes->get(1);

                $clockIn = $attendance->clock_in_time
                    ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i')
                    : '';

                $clockOut = $attendance->clock_out_time
                    ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i')
                    : '';

                $break1Start = $break1?->break_start_time
                    ? \Carbon\Carbon::parse($break1->break_start_time)->format('H:i')
                    : '';

                $break1End = $break1?->break_end_time
                    ? \Carbon\Carbon::parse($break1->break_end_time)->format('H:i')
                    : '';

                $break2Start = $break2?->break_start_time
                    ? \Carbon\Carbon::parse($break2->break_start_time)->format('H:i')
                    : '';

                $break2End = $break2?->break_end_time
                    ? \Carbon\Carbon::parse($break2->break_end_time)->format('H:i')
                    : '';
            @endphp

            <div class="detail-card">
                <table class="detail-table">

                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>
                            <div class="date-row">
                                <span class="date-year">
                                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}
                                </span>
                                <span class="date-md">
                                    {{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}
                                </span>
                            </div>
                            <input type="hidden" name="work_date" value="{{ $attendance->work_date }}">
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td class="time-inputs">
                            <input type="text" name="requested_clock_in_time" class="time-box" value="{{ old('requested_clock_in_time', $clockIn) }}">
                            <span class="time-sep">〜</span>
                            <input type="text" name="requested_clock_out_time" class="time-box" value="{{ old('requested_clock_out_time', $clockOut) }}">
                        </td>
                    </tr>

                    <tr>
                        <th>休憩</th>
                        <td class="time-inputs">
                            <input type="text" name="breaks[0][start]" class="time-box" value="{{ old('breaks.0.start', $break1Start) }}">
                            <span class="time-sep">〜</span>
                            <input type="text" name="breaks[0][end]" class="time-box" value="{{ old('breaks.0.end', $break1End) }}">
                        </td>
                    </tr>

                    <tr>
                        <th>休憩2</th>
                        <td class="time-inputs">
                            <input type="text" name="breaks[1][start]" class="time-box" value="{{ old('breaks.1.start', $break2Start) }}">
                            <span class="time-sep">〜</span>
                            <input type="text" name="breaks[1][end]" class="time-box" value="{{ old('breaks.1.end', $break2End) }}">
                        </td>
                    </tr>

                    <tr>
                        <th>備考</th>
                        <td>
                            <input type="text" name="requested_note" class="note-box" value="{{ old('requested_note', $attendance->note) }}">
                        </td>
                    </tr>

                </table>
            </div>

            <div class="detail-actions">
                <button type="submit" class="btn-fix">修正</button>
            </div>

        </form>

    </div>
</div>
@endsection