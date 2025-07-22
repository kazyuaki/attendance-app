@extends('layouts.admin_app')

@section('css')
    <link rel="stylesheet" href="../../../css/common.css">
    <link rel="stylesheet" href="../../../css/admin/login.css">
@endsection


@section('content')
    <main class="main">
        <h1 class="main__title">管理者ログイン</h1>
        <form action="{{ route('admin.login') }}" method="post" class="login-form">
            @csrf
            <div class="form-group">
                <label for="email" class="form-group__label">メールアドレス</label>
                <input type="email" id="email" name="email" required class="form-group__input">
            </div>

            <div class="form-group">
                <label for="password" class="form-group__label">パスワード</label>
                <input type="password" id="password" name="password" required class="form-group__input">
            </div>

            <button type="submit" class="login__button">管理者ログインする</button>
        </form>
    </main>
@endsection