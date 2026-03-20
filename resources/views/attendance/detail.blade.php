@extends('layouts.app')

@section('content')
<div class="attendance-detail-page">
    <div class="attendance-container">

        <h2 class="page-title">勤怠詳細</h2>

        @if ($errors->any())
            <div style="margin:16px 0; padding:12px; border:1px solid #b00; border-radius:8px; color:#b00;">
                <ul style="margin:0; padding-left:18px;">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @php
            $break1 = $breaks->get(0);
            $break2 = $breaks->get(1);

            $pending = $pendingRequest && $pendingRequest->status === 'pending'
                ? $pendingRequest
                : null;

            $clockIn = old(
                'requested_clock_in_time',
                $pending?->requested_clock_in_time
                    ? \Carbon\Carbon::parse($pending->requested_clock_in_time)->format('H:i')
                    : ($attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '')
            );

            $clockOut = old(
                'requested_clock_out_time',
                $pending?->requested_clock_out_time
                    ? \Carbon\Carbon::parse($pending->requested_clock_out_time)->format('H:i')
                    : ($attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '')
            );

            $break1Start = old(
                'breaks.0.start',
                $break1?->break_start_time ? \Carbon\Carbon::parse($break1->break_start_time)->format('H:i') : ''
            );

            $break1End = old(
                'breaks.0.end',
                $break1?->break_end_time ? \Carbon\Carbon::parse($break1->break_end_time)->format('H:i') : ''
            );

            $break2Start = old(
                'breaks.1.start',
                $break2?->break_start_time ? \Carbon\Carbon::parse($break2->break_start_time)->format('H:i') : ''
            );

            $break2End = old(
                'breaks.1.end',
                $break2?->break_end_time ? \Carbon\Carbon::parse($break2->break_end_time)->format('H:i') : ''
            );

            $note = old(
                'requested_note',
                $pending?->requested_note ?? ($attendance->note ?? '')
            );

            $showBreak2Row = !$pending || ($break2Start !== '' || $break2End !== '');
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
                            <div class="date-row">
                                <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                                <span>{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td class="time-inputs">
                            @if ($pending)
                                <span>{{ \Carbon\Carbon::parse($pending->requested_clock_in_time)->format('H:i') }}</span>
                                <span class="time-sep">〜</span>
                                <span>{{ \Carbon\Carbon::parse($pending->requested_clock_out_time)->format('H:i') }}</span>
                            @else
                                <input type="text" name="requested_clock_in_time" class="time-box" value="{{ $clockIn }}">
                                <span class="time-sep">〜</span>
                                <input type="text" name="requested_clock_out_time" class="time-box" value="{{ $clockOut }}">
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>休憩</th>
                        <td class="time-inputs">
                            @if ($pending)
                                <span>
                                    {{ $pending->requested_break_start_time ? \Carbon\Carbon::parse($pending->requested_break_start_time)->format('H:i') : '' }}
                                </span>
                                <span class="time-sep">〜</span>
                                <span>
                                    {{ $pending->requested_break_end_time ? \Carbon\Carbon::parse($pending->requested_break_end_time)->format('H:i') : '' }}
                                </span>
                            @else
                                <input type="text" name="breaks[0][start]" class="time-box" value="{{ $break1Start }}">
                                <span class="time-sep">〜</span>
                                <input type="text" name="breaks[0][end]" class="time-box" value="{{ $break1End }}">
                            @endif
                        </td>
                    </tr>

                    @if ($showBreak2Row)
                        <tr>
                            <th>休憩2</th>
                            <td class="time-inputs">
                                @if ($pending)
                                    <span>-</span>
                                    <span class="time-sep">〜</span>
                                    <span>-</span>
                                @else
                                    <input type="text" name="breaks[1][start]" class="time-box" value="{{ $break2Start }}">
                                    <span class="time-sep">〜</span>
                                    <input type="text" name="breaks[1][end]" class="time-box" value="{{ $break2End }}">
                                @endif
                            </td>
                        </tr>
                    @endif

                    <tr>
                        <th>備考</th>
                        <td>
                            @if ($pending)
                                <span>{{ $note }}</span>
                            @else
                                <input type="text" name="requested_note" class="note-box" value="{{ $note }}">
                            @endif
                        </td>
                    </tr>

                </table>
            </div>

            @if ($pending)
                <p class="pending-message">※承認待ちのため修正はできません。</p>
            @else
                <div class="detail-actions">
                    <button type="submit" class="btn-fix">修正</button>
                </div>
            @endif

        </form>

    </div>
</div>
@endsection