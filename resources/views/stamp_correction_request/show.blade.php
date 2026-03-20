@extends('layouts.app')

@section('content')
<div class="request-detail-page">
    <div class="request-detail-container">
        <h1 class="page-title">申請詳細</h1>

        <div class="detail-card">
            <table class="detail-table">
                <tbody>

                    <tr>
                        <th>名前</th>
                        <td>{{ $correctionRequest->user?->name ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>
                            @if($correctionRequest->attendance?->work_date)
                                @php
                                    $workDate = \Carbon\Carbon::parse($correctionRequest->attendance->work_date);
                                @endphp
                                <div class="date-row">
                                    <span>{{ $workDate->format('Y年') }}</span>
                                    <span>{{ $workDate->format('n月j日') }}</span>
                                </div>
                            @else
                                -
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <div class="time-range">
                                <span>
                                    {{ $correctionRequest->requested_clock_in_time
                                        ? \Carbon\Carbon::parse($correctionRequest->requested_clock_in_time)->format('H:i')
                                        : '-' }}
                                </span>

                                <span class="time-separator">〜</span>

                                <span>
                                    {{ $correctionRequest->requested_clock_out_time
                                        ? \Carbon\Carbon::parse($correctionRequest->requested_clock_out_time)->format('H:i')
                                        : '-' }}
                                </span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>休憩</th>
                        <td>
                            <div class="time-range">
                                <span>
                                    {{ $correctionRequest->requested_break_start_time
                                        ? \Carbon\Carbon::parse($correctionRequest->requested_break_start_time)->format('H:i')
                                        : '-' }}
                                </span>

                                <span class="time-separator">〜</span>

                                <span>
                                    {{ $correctionRequest->requested_break_end_time
                                        ? \Carbon\Carbon::parse($correctionRequest->requested_break_end_time)->format('H:i')
                                        : '-' }}
                                </span>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th>備考</th>
                        <td>{{ $correctionRequest->requested_note ?? '-' }}</td>
                    </tr>

                </tbody>
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