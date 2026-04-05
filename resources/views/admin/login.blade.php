@extends('layouts.app')

@section('content')
<div class="container">
    <h2>ログイン</h2>

    @if ($errors->any())
        <div style="margin:16px 0; padding:12px; border:1px solid #b00; border-radius:8px; color:#b00;">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.process') }}">
    @csrf

    <div class="form-group">
        <label>メールアドレス</label>
        <input type="email" name="email" value="{{ old('email') }}">
    </div>

    <div class="form-group">
        <label>パスワード</label>
        <input type="password" name="password">
    </div>

    <button type="submit">管理者ログインする</button>
</form>




</div>
@endsection