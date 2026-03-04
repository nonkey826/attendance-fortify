<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/request.css') }}">
    <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
</head>
<body>

<header class="header">
    <div class="logo">
        <img src="{{ asset('images/COACHTECH.png') }}" alt="COACHTECH">
    </div>

    @auth
    <nav class="nav">
        @if (auth()->user()->role === 'admin')
            <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('admin.staff.list') }}">スタッフ一覧</a>
            <a href="{{ route('stamp_correction_request.list') }}">申請一覧</a>
        @else
            <a href="{{ route('attendance.index') }}">勤怠</a>
            <a href="{{ route('attendance.list') }}">勤怠一覧</a>
            <a href="{{ route('stamp_correction_request.list') }}">申請</a>
        @endif

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="logout-btn">ログアウト</button>
        </form>
    </nav>
    @endauth
</header>

<main class="main">
    @yield('content')
</main>

</body>
</html>