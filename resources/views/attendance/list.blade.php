@extends('layouts.app')

@section('content')
<div class="attendance-list-page">

    <div class="attendance-container">

        <h2 class="page-title">勤怠一覧</h2>

{{-- 月ナビ --}}
@php
    $currentMonth = request('month', now()->format('Y-m'));
    $base = \Carbon\Carbon::parse($currentMonth . '-01');
@endphp

<div class="month-card">

    {{-- 前月 --}}
    <a class="month-link"
       href="{{ route('attendance.list') }}?month={{ $base->copy()->subMonth()->format('Y-m') }}">
        <img src="{{ asset('images/arrow.png') }}" class="arrow-left">
        前月
    </a>

    {{-- 月表示 --}}
    <div class="month-label">
        <img src="{{ asset('images/month.png') }}" class="calendar-icon">
        {{ $base->format('Y/m') }}
    </div>

    {{-- 翌月 --}}
    <a class="month-link"
       href="{{ route('attendance.list') }}?month={{ $base->copy()->addMonth()->format('Y-m') }}">
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
        <a href="{{ route('attendance.detail', $row['attendance']->id) }}">
            詳細
        </a>
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