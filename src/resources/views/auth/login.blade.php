@extends('layouts/guest')

@section('css')
<link rel="stylesheet" href="../../css/admin/login.css">
@endsection
</head>

@section('title', 'ユーザー ログイン | 勤怠管理システム')


@section('content')
<main class="main">
    <h1 class="main__title">ログイン</h1>
    <form action="{{ route('login') }}" method="post" class="login-form" novalidate>
        @csrf

        <div class="form-group">
            <label for="email" class="form-group__label">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-group__input" value="{{ old('email') }}">
            @error('email')
            <div class="form-error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-group__label">パスワード</label>
            <input type="password" id="password" name="password" class="form-group__input">
            @error('password')
            <div class="form-error-message">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="login__button">ログインする</button>

        <a href="/register" class="register__link">会員登録はこちら</a>
    </form>
</main>
@endsection