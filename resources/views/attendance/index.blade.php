<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>打刻画面</title>
</head>
<body>

<h1>打刻画面</h1>


@if(!$attendance)
    <form method="POST" action="/attendance/clock-in">
        @csrf
        <button type="submit">出勤</button>
    </form>
@else

    <p>出勤時間: {{ $attendance->clock_in_time }}</p>

    @if($attendance->clock_out_time)
        <p>退勤時間: {{ $attendance->clock_out_time }}</p>
        <p>本日の勤務は終了しています。</p>
    @else

        @if(!$activeBreak)
            <form method="POST" action="/attendance/break-start">
                @csrf
                <button type="submit">休憩開始</button>
            </form>
        @else
            <p>休憩中: {{ $activeBreak->break_start_time }}</p>

            <form method="POST" action="/attendance/break-end">
                @csrf
                <button type="submit">休憩終了</button>
            </form>
        @endif

        <form method="POST" action="/attendance/clock-out">
            @csrf
            <button type="submit">退勤</button>
        </form>

    @endif

@endif




</body>
</html>
