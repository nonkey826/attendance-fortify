@extends('layouts.app')

@section('content')
<div class="attendance-list-page">

    <div class="attendance-container">

        <h2 class="page-title">勤怠一覧</h2>

        {{-- 月ナビ --}}
        <div class="month-card">

    <a class="month-link"
       href="{{ route('attendance.list', ['month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}">
        <img src="{{ asset('images/arrow.png') }}" class="arrow-left">
        前月
    </a>

    <div class="month-label">
    <img src="{{ asset('images/month.png') }}" class="calendar-icon">
    {{ \Carbon\Carbon::parse($month)->format('Y/m') }}
</div>

    <a class="month-link"
       href="{{ route('attendance.list', ['month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}">
        翌月
        <img src="{{ asset('images/arrow.png') }}" class="arrow-right">
    </a>

</div>

        {{-- テーブルカード --}}
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
                    @foreach($data as $row)
                        <tr>
                            <td>{{ $row['date']->isoFormat('MM/DD(ddd)') }}</td>
                            <td>{{ $row['clock_in'] ?? '' }}</td>
                            <td>{{ $row['clock_out'] ?? '' }}</td>
                            <td>{{ $row['break'] ?? '' }}</td>
                            <td>{{ $row['total'] ?? '' }}</td>
                            <td>
                                @if($row['attendance'])
                                    <a href="#">詳細</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection