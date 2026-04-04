@extends('layouts.app')

@section('content')
<div class="attendance-detail-page">
    <div class="attendance-container">

        <h2 class="page-title">勤怠詳細</h2>

        @if ($errors->any())
            <div style="margin:16px 0; padding:12px; border:1px solid #b00; border-radius:8px; color:#b00;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach (collect($errors->all())->unique() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $clockIn = $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '';
            $clockOut = $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '';

            $break1 = $attendance->breakTimes[0] ?? null;
            $break2 = $attendance->breakTimes[1] ?? null;
        @endphp

        <form method="POST" action="{{ route('stamp_correction_request.store', $attendance->id) }}">
            @csrf

            <div class="detail-card">
                <table class="detail-table">

                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>
                            {{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td class="time-inputs">
                            <input type="text" name="requested_clock_in_time" class="time-box" value="{{ $clockIn }}">
                            <span class="time-sep">〜</span>
                            <input type="text" name="requested_clock_out_time" class="time-box" value="{{ $clockOut }}">
                        </td>
                    </tr>

                    {{-- 休憩① --}}
                    <tr>
                        <th>休憩</th>
                        <td class="time-inputs">
                            <input type="text" name="breaks[0][start]" class="time-box"
                                value="{{ $break1?->break_start_time ? \Carbon\Carbon::parse($break1->break_start_time)->format('H:i') : '' }}">
                            <span class="time-sep">〜</span>
                            <input type="text" name="breaks[0][end]" class="time-box"
                                value="{{ $break1?->break_end_time ? \Carbon\Carbon::parse($break1->break_end_time)->format('H:i') : '' }}">
                        </td>
                    </tr>

                    {{-- 休憩② --}}
                    <tr>
                        <th>休憩2</th>
                        <td class="time-inputs">
                            <input type="text" name="breaks[1][start]" class="time-box"
                                value="{{ $break2?->break_start_time ? \Carbon\Carbon::parse($break2->break_start_time)->format('H:i') : '' }}">
                            <span class="time-sep">〜</span>
                            <input type="text" name="breaks[1][end]" class="time-box"
                                value="{{ $break2?->break_end_time ? \Carbon\Carbon::parse($break2->break_end_time)->format('H:i') : '' }}">
                        </td>
                    </tr>

                    <tr>
                        <th>備考</th>
                        <td>
                            <input type="text" name="requested_note" class="note-box" value="{{ $attendance->note ?? '' }}">
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