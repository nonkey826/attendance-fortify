@extends('layouts.app')

@section('content')
<h1>ログイン</h1>

<form action="/login" method="post">
    @csrf

    <div>
        <label>メールアドレス</label><br>
        <input type="email" name="email" value="{{ old('email') }}" required>
    </div>

    <div>
        <label>パスワード</label><br>
        <input type="password" name="password" required>
    </div>

    <button type="submit">ログイン</button>
</form>

<p><a href="/register">新規登録はこちら</a></p>
@endsection
