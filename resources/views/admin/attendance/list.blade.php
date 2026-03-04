@extends('layouts.app')

@section('content')
@php
    // Controllerから $date は "YYYY-MM-DD" の文字列で来る前提
    $d = \Carbon\Carbon::parse($date);
@endphp

<div class="attendance-list-page">
    <div class="attendance-container">

        <h2 class="page-title">
            {{ $d->isoFormat('YYYY年M月D日の勤怠') }}
        </h2>

        {{-- 日付ナビ（前日 / 当日 / 翌日） --}}
        <div class="month-card">

            <a class="month-link"
               href="{{ route('admin.attendance.list', ['date' => $d->copy()->subDay()->format('Y-m-d')]) }}">
                <img src="{{ asset('images/arrow.png') }}" class="arrow-left">
                前日
            </a>

            <div class="month-label">
                <img src="{{ asset('images/month.png') }}" class="calendar-icon">
                {{ $d->format('Y/m/d') }}
            </div>

            <a class="month-link"
               href="{{ route('admin.attendance.list', ['date' => $d->copy()->addDay()->format('Y-m-d')]) }}">
                翌日
                <img src="{{ asset('images/arrow.png') }}" class="arrow-right">
            </a>

        </div>

        {{-- テーブル --}}
        <div class="table-card">
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>名前</th>
                        <th>出勤</th>
                        <th>退勤</th>
                        <th>休憩</th>
                        <th>合計</th>
                        <th>詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row['name'] ?? '' }}</td>
                            <td>{{ $row['clock_in'] ?? '' }}</td>
                            <td>{{ $row['clock_out'] ?? '' }}</td>
                            <td>{{ $row['break'] ?? '' }}</td>
                            <td>{{ $row['total'] ?? '' }}</td>
                            <td>
                                @if (!empty($row['attendance_id']))
                                    <a href="{{ route('admin.attendance.detail', $row['attendance_id']) }}">
                                        詳細
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:24px;">
                                データがありません
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>
</div>
@endsection