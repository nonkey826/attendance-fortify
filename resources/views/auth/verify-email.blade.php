@extends('layouts.app')

@section('content')
<div style="
    background:#fff;
    min-height: calc(100vh - 60px);
    display:flex;
    align-items:center;
    justify-content:center;
">
    <div style="text-align:center;">

        <!-- 説明文 -->
        <p style="
    font-size:24px;
    font-weight:700;
    line-height:120%;
    margin-bottom:40px;
    padding:0 20px;
">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <!-- 認証はこちらからボタン -->
        <div style="margin-bottom:30px;">
        <a href="http://localhost:8025"
   style="
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:100%;
        max-width:257px;
        height:69px;
        font-size:24px;
        font-weight:700;
        border-radius:10px;
        border:1px solid #000;
        text-decoration:none;
        color:#000;
        background:#E5E5E5;
   ">
    認証はこちらから
</a>
            
        </div>

        <!-- 再送リンク -->
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit"
                style="
                    background:none;
                    border:none;
                    font-size:20px;
                    font-weight:400;
                    color:#007bff;
                    cursor:pointer;
                    text-decoration:none;
                ">
                認証メールを再送する
            </button>
        </form>

    </div>
</div>
@endsection
