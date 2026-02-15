<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>勤怠一覧</title>
</head>
<body>

<h1>勤怠一覧（{{ $month }}）</h1>

<table border="1" cellpadding="5">
    <tr>
        <th>日付</th>
        <th>出勤</th>
        <th>退勤</th>
        <th>ステータス</th>
    </tr>

    @foreach($data as $row)
        <tr>
            <td>{{ $row['date']->format('m/d') }}</td>

            @if($row['attendance'])
                <td>{{ $row['attendance']->clock_in_time }}</td>
                <td>{{ $row['attendance']->clock_out_time }}</td>
                <td>{{ $row['attendance']->status }}</td>
            @else
                <td>-</td>
                <td>-</td>
                <td>未出勤</td>
            @endif
        </tr>
    @endforeach

</table>

</body>
</html>
