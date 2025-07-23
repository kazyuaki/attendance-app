@extends('layouts/guest')

@section('css')
<link rel="stylesheet" href="../../css/admin/login.css">
@endsection
</head>


@section('content')
<main class="main">
    <h1 class="main__title">ログイン</h1>
    <form action="" method="post" class="login-form">
        <div class="form-group">
            <label for="email" class="form-group__label">メールアドレス</label>
            <input type="email" id="email" name="email" required class="form-group__input">
        </div>

        <div class="form-group">
            <label for="password" class="form-group__label">パスワード</label>
            <input type="password" id="password" name="password" required class="form-group__input">
        </div>

        <button type="submit" class="login__button">ログインする</button>

        <a href="/register" class="register__link">会員登録はこちら</a>
    </form>
</main>
@endsection