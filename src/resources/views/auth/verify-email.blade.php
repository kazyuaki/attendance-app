@extends('layouts.guest')

@section('title', 'ユーザー メール認証 | 勤怠管理システム')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css' )}}">

@section('content')
<div class="verify-email__content">
    @if (session('status') == 'verification-link-sent')
    <p class="verify-email__message">再送しました。メールをご確認ください。</p>
    @else

    <p class="verify-email__text">
        登録していただいたメールアドレスに認証メールを送付しました。<br>
        メール認証を完了してください。
    </p>

    {{-- 認証はこちらから（リンク先は任意。ここでは /mypage） --}}
    <a href="http://localhost:8025" class="verify-email__button" target="_blank">認証はこちらから</a>
    {{-- 認証メール再送フォーム --}}
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="verify-email__resend-button">認証メールを再送する</button>
    </form>
    @endif
</div>
@endsection