@extends('layouts.app')

@section('content')

<div class="container">
    <h2>ログイン</h2>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input 
                type="email" 
                id="email"
                name="email" 
                value="{{ old('email') }}"
            >
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input 
                type="password" 
                id="password"
                name="password"
            >
            @error('password')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">ログインする</button>
    </form>

    <div class="link-area">
        <a href="/register" class="link">
            会員登録はこちら
        </a>
    </div>
</div>

@endsection

