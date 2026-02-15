@extends('layouts.app')

@section('content')
<div class="container">
    <h2>会員登録</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label>ユーザー名</label>
            <input type="text" name="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label>メールアドレス</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div class="form-group">
            <label>パスワード</label>
            <input type="password" name="password" required>
        </div>

        <div class="form-group">
            <label>パスワード（確認）</label>
            <input type="password" name="password_confirmation" required>
        </div>

        <button type="submit">登録する</button>
    </form>

    <a href="{{ route('login') }}" class="link">ログインはこちら</a>
</div>
@endsection

