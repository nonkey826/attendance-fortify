@extends('layouts.app')

@section('content')
<div class="attendance-page">
    <div class="detail-container">

        <h2 class="page-title">勤怠詳細</h2>

        <div class="detail-card">
            <table class="detail-table">
                <tr>
                    <th>日付</th>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
                </tr>

                <tr>
                    <th>出勤</th>
                    <td>
                        {{ $attendance->clock_in_time 
                            ? \Carbon\Carbon::parse($attendance->clock_in_time)->format('H:i')
                            : '' }}
                    </td>
                </tr>

                <tr>
                    <th>退勤</th>
                    <td>
                        {{ $attendance->clock_out_time 
                            ? \Carbon\Carbon::parse($attendance->clock_out_time)->format('H:i')
                            : '' }}
                    </td>
                </tr>

                <tr>
                    <th>備考</th>
                    <td>{{ $attendance->note ?? '' }}</td>
                </tr>
            </table>
        </div>

    </div>
</div>
@endsection