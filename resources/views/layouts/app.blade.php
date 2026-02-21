<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<header class="header">
    <div class="logo">COACHTECH</div>

    @auth
    <nav class="nav">
        <a href="{{ route('attendance.index') }}">勤怠</a>
        <a href="{{ route('attendance.list') }}">勤怠一覧</a>
        <a href="#">申請</a>

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