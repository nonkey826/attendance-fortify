@extends('layouts.app')

@section('content')
<h1>新規登録</h1>

<form action="/register" method="post">
    @csrf

    <div>
        <label>ユーザー名</label><br>
        <input type="text" name="name" value="{{ old('name') }}" required>
    </div>

    <div>
        <label>メールアドレス</label><br>
        <input type="email" name="email" value="{{ old('email') }}" required>
    </div>

    <div>
        <label>パスワード</label><br>
        <input type="password" name="password" required>
    </div>

    <div>
        <label>パスワード（確認）</label><br>
        <input type="password" name="password_confirmation" required>
    </div>

    <button type="submit">登録</button>
</form>

<p><a href="/login">ログインはこちら</a></p>
@endsection
