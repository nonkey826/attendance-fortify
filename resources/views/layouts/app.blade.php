<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH</title>

    <!-- 認証画面用CSS -->
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

    <!-- ヘッダー -->
    <header class="header">
        <div class="logo">COACHTECH</div>

        @auth
            <form method="POST" action="{{ route('logout') }}" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">ログアウト</button>
            </form>
        @endauth
    </header>

    <!-- メイン -->
    <main class="main">
        @yield('content')
    </main>

</body>
</html>
