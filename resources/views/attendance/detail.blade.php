@extends('layouts.app')



@section('content')
<div class="attendance-detail-page">
    <div class="attendance-container">

        <h2 class="page-title">勤怠詳細</h2>

        {{-- バリデーションエラー --}}
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

            $clockIn = old(
                'requested_clock_in_time',
                $attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : ''
            );

            $clockOut = old(
                'requested_clock_out_time',
                $attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : ''
            );

            $break1Start = old('breaks.0.start', $break1?->break_start_time ? \Carbon\Carbon::parse($break1->break_start_time)->format('H:i') : '');
            $break1End   = old('breaks.0.end',   $break1?->break_end_time   ? \Carbon\Carbon::parse($break1->break_end_time)->format('H:i') : '');

            $break2Start = old('breaks.1.start', $break2?->break_start_time ? \Carbon\Carbon::parse($break2->break_start_time)->format('H:i') : '');
            $break2End   = old('breaks.1.end',   $break2?->break_end_time   ? \Carbon\Carbon::parse($break2->break_end_time)->format('H:i') : '');

            $note = old(
    'requested_note',
    $pendingRequest?->requested_note ?? ($attendance->note ?? '')
);

            // pending時に休憩2を表示するか（空なら非表示）
            $showBreak2Row = !$pendingRequest || ($break2Start !== '' || $break2End !== '');
        @endphp

        {{-- 修正申請フォーム --}}
        <form method="POST" action="{{ route('stamp_correction_request.store', $attendance->id) }}">
            @csrf

            <div class="detail-card">
                <table class="detail-table">

                    {{-- 名前（表示のみ） --}}
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>

                    {{-- 日付（表示） --}}
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
                        </td>
                    </tr>

                    {{-- 出勤・退勤 --}}
                    <tr>
                        <th>出勤・退勤</th>
                        <td class="time-inputs">
                            @if ($pendingRequest)
                                <span>{{ $clockIn }}</span>
                                <span class="time-sep">〜</span>
                                <span>{{ $clockOut }}</span>
                            @else
                                <input
                                    type="text"
                                    name="requested_clock_in_time"
                                    class="time-box"
                                    inputmode="numeric"
                                    placeholder="09:00"
                                    value="{{ $clockIn }}"
                                >
                                <span class="time-sep">〜</span>
                                <input
                                    type="text"
                                    name="requested_clock_out_time"
                                    class="time-box"
                                    inputmode="numeric"
                                    placeholder="18:00"
                                    value="{{ $clockOut }}"
                                >
                            @endif
                        </td>
                    </tr>

                    {{-- 休憩 --}}
                    <tr>
                        <th>休憩</th>
                        <td class="time-inputs">
                            @if ($pendingRequest)
                                <span>{{ $break1Start }}</span>
                                <span class="time-sep">〜</span>
                                <span>{{ $break1End }}</span>
                            @else
                                <input
                                    type="text"
                                    name="breaks[0][start]"
                                    class="time-box"
                                    inputmode="numeric"
                                    placeholder="12:00"
                                    value="{{ $break1Start }}"
                                >
                                <span class="time-sep">〜</span>
                                <input
                                    type="text"
                                    name="breaks[0][end]"
                                    class="time-box"
                                    inputmode="numeric"
                                    placeholder="13:00"
                                    value="{{ $break1End }}"
                                >
                            @endif
                        </td>
                    </tr>

                    {{-- 休憩2（pending時は空なら行ごと非表示） --}}
                    @if ($showBreak2Row)
                        <tr>
                            <th>休憩2</th>
                            <td class="time-inputs">
                                @if ($pendingRequest)
                                    <span>{{ $break2Start }}</span>
                                    <span class="time-sep">〜</span>
                                    <span>{{ $break2End }}</span>
                                @else
                                    <input
                                        type="text"
                                        name="breaks[1][start]"
                                        class="time-box"
                                        inputmode="numeric"
                                        placeholder="12:00"
                                        value="{{ $break2Start }}"
                                    >
                                    <span class="time-sep">〜</span>
                                    <input
                                        type="text"
                                        name="breaks[1][end]"
                                        class="time-box"
                                        inputmode="numeric"
                                        placeholder="13:00"
                                        value="{{ $break2End }}"
                                    >
                                @endif
                            </td>
                        </tr>
                    @endif

                    {{-- 備考 --}}
                    <tr>
    <th>備考</th>
    <td>
        @if ($pendingRequest)
            <span>{{ $note }}</span>
        @else
            <input
                type="text"
                name="requested_note"
                class="note-box"
                value="{{ $note }}"
            >
        @endif
    </td>
</tr>

                </table>
            </div>

            {{-- 承認待ちメッセージ（pending時のみ表示） --}}
            @if ($pendingRequest)
                <p class="pending-message">※承認待ちのため修正はできません。</p>
            @endif

            {{-- 修正ボタン：pending時は非表示 --}}
            @unless ($pendingRequest)
                <div class="detail-actions">
                    <button type="submit" class="btn-fix">修正</button>
                </div>
            @endunless

        </form>

    </div>
</div>
@endsection