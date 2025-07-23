@extends('layouts/guest')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
@endsection

@section('content')
<main class="register">
    <h1 class="main__title">会員登録</h1>
    <form action="" method="post" class="register-form">
        <div class="form-group">
            <label for="name" class="form-group__label">名前</label>
            <input type="text" id="name" name="name" required class="form-group__input">
        </div>
        <div class="form-group">
            <label for="email" class="form-group__label">メールアドレス</label>
            <input type="email" id="email" name="email" required class="form-group__input">
        </div>

        <div class="form-group">
            <label for="password" class="form-group__label">パスワード</label>
            <input type="password" id="password" name="password" required class="form-group__input">
        </div>
        <div class="form-group">
            <label for="password_confirmation" class="form-group__label">パスワード確認</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required class="form-group__input">
        </div>

        <button type="submit" class="register__button">登録する</button>

        <a href="/login" class="login__link">ログインはこちら</a>
    </form>
</main>
@endsection