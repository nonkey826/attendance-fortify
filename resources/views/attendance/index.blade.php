@extends('layouts.app')

@section('content')
<div class="attendance-page">
<div class="attendance-wrapper">

    {{-- ステータスバッジ --}}
    <div class="status-badge">
        {{ $status }}
    </div>

    {{-- 日付 --}}
    <div class="date">
        {{ $now->isoFormat('Y年M月D日(ddd)') }}
    </div>

    {{-- 時刻 --}}
    <div class="time">
        {{ $now->format('H:i') }}
    </div>

    {{-- ボタンエリア --}}
    <div class="button-area">

        @if($status === '勤務外')
            <form method="POST" action="{{ route('attendance.clockIn') }}">
                @csrf
                <button class="btn-primary">出勤</button>
            </form>
        @endif

        @if($status === '出勤中')
            <form method="POST" action="{{ route('attendance.clockOut') }}">
                @csrf
                <button class="btn-primary">退勤</button>
            </form>

            <form method="POST" action="{{ route('attendance.breakStart') }}">
                @csrf
                <button class="btn-secondary">休憩入</button>
            </form>
        @endif

        @if($status === '休憩中')
            <form method="POST" action="{{ route('attendance.breakEnd') }}">
                @csrf
                <button class="btn-secondary">休憩戻</button>
            </form>
        @endif

        @if($status === '退勤済')
            <div class="message">お疲れ様でした。</div>
        @endif

    </div>
</div>
</div>

@endsection