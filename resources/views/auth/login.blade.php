<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>ログイン</title>

    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

<div class="header">
    <h1>COACHTECH</h1>
</div>

<div class="container">
    <h2>ログイン</h2>

    <form method="POST" action="/login">
        @csrf

        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email">
        </div>

        <div class="form-group">
            <label>パスワード</label>
            <input type="password" name="password">
        </div>

        <button type="submit">ログインする</button>
    </form>

    <a href="/register" class="link">会員登録はこちら</a>
</div>

</body>
</html>
