<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title','管理者')</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                <a href="">
                    <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH" width="350">
                </a>
            </div>

            <div class="nav-links">

                <a href="{{ route('admin.attendances.index') }}">勤怠一覧</a>
                <a href="{{ route('admin.users.index') }}">スタッフ一覧</a>
                <a href="#">申請一覧</a>
                <a href="/admin/login">ログアウト</a>
            </div>

        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>