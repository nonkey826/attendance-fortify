@extends('layouts.app')

@section('content')
<div class="request-detail-page">
    <div class="request-detail-container">
        <h1 class="page-title">申請詳細</h1>

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
                    <td>
                        {{ $correctionRequest->requested_clock_in_time 
                            ? \Carbon\Carbon::parse($correctionRequest->requested_clock_in_time)->format('H:i') 
                            : ($attendance->clock_in_time ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i') : '') }}
                        〜
                        {{ $correctionRequest->requested_clock_out_time 
                            ? \Carbon\Carbon::parse($correctionRequest->requested_clock_out_time)->format('H:i') 
                            : ($attendance->clock_out_time ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i') : '') }}
                    </td>
                </tr>

                @php
                    $starts = json_decode($correctionRequest->requested_break_start_time, true) ?? [];
                    $ends = json_decode($correctionRequest->requested_break_end_time, true) ?? [];
                @endphp

                {{-- 休憩 --}}
                @if(count($starts) > 0)
                    @foreach($starts as $index => $start)
                    <tr>
                        <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                        <td>
                            {{ $start ? \Carbon\Carbon::parse($start)->format('H:i') : '' }}
                            〜
                            {{ isset($ends[$index]) && $ends[$index] ? \Carbon\Carbon::parse($ends[$index])->format('H:i') : '' }}
                        </td>
                    </tr>
                    @endforeach
                @else
                    @foreach($attendance->breakTimes as $index => $break)
                    <tr>
                        <th>休憩{{ $index === 0 ? '' : $index + 1 }}</th>
                        <td>
                            {{ $break->break_start_time ? \Carbon\Carbon::parse($break->break_start_time)->format('H:i') : '' }}
                            〜
                            {{ $break->break_end_time ? \Carbon\Carbon::parse($break->break_end_time)->format('H:i') : '' }}
                        </td>
                    </tr>
                    @endforeach
                @endif

                <tr>
                    <th>備考</th>
                    <td>{{ $correctionRequest->requested_note ?? $attendance->note }}</td>
                </tr>

            </table>
        </div>

        @if (auth()->user()->role === 'admin' && $correctionRequest->status === 'pending')
            <div class="detail-actions">
                <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $correctionRequest->id) }}">
                    @csrf
                    <button type="submit" class="approve-button">承認</button>
                </form>
            </div>
        @else
            <div class="detail-actions">
                @if($correctionRequest->status === 'pending')
                    <div class="pending-message">*承認待ちのため修正できません</div>
                @else
                    <div class="approved-label">承認済み</div>
                @endif
            </div>
        @endif

    </div>
</div>
@endsection