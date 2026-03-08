@extends('layouts.app')

@section('content')
<div class="attendance-detail-page">
    <div class="attendance-container">

        <h2 class="page-title">勤怠詳細</h2>

        <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
            @csrf

            @php
                $break = $attendance->breakTimes->first();

                $clockIn = $attendance->clock_in_time
                    ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i')
                    : '';

                $clockOut = $attendance->clock_out_time
                    ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i')
                    : '';

                $breakStart = $break?->break_start_time
                    ? \Carbon\Carbon::parse($break->break_start_time)->format('H:i')
                    : '';

                $breakEnd = $break?->break_end_time
                    ? \Carbon\Carbon::parse($break->break_end_time)->format('H:i')
                    : '';
            @endphp

            <div class="detail-card">
                <table class="detail-table">

                    {{-- 名前 --}}
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>

                    {{-- 日付 --}}
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

                            {{-- DB更新用 --}}
                            <input type="hidden" name="work_date" value="{{ $attendance->work_date }}">
                        </td>
                    </tr>

                    {{-- 出勤・退勤 --}}
                    <tr>
                        <th>出勤・退勤</th>
                        <td class="time-inputs">

                            <input
                                type="time"
                                name="clock_in_time"
                                class="time-box"
                                value="{{ $clockIn }}"
                            >

                            <span class="time-sep">〜</span>

                            <input
                                type="time"
                                name="clock_out_time"
                                class="time-box"
                                value="{{ $clockOut }}"
                            >

                        </td>
                    </tr>

                    {{-- 休憩 --}}
                    <tr>
                        <th>休憩</th>
                        <td class="time-inputs">

                            <input
                                type="time"
                                name="break_start_time"
                                class="time-box"
                                value="{{ $breakStart }}"
                            >

                            <span class="time-sep">〜</span>

                            <input
                                type="time"
                                name="break_end_time"
                                class="time-box"
                                value="{{ $breakEnd }}"
                            >

                        </td>
                    </tr>

                    {{-- 備考 --}}
                    <tr>
                        <th>備考</th>
                        <td>
                            <input
                                type="text"
                                name="note"
                                class="note-box"
                                value="{{ $attendance->note }}"
                            >
                        </td>
                    </tr>

                </table>
            </div>

            <div class="detail-actions">
                <button type="submit" class="btn-fix">保存</button>
            </div>

        </form>

    </div>
</div>
@endsection