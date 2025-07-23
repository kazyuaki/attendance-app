<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance app</title>
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__logo">
                <a href="">
                    <img src="../../img/logo.svg" alt="COACHTECH" width="350">
                </a>
            </div>

            <div class="nav-links">

                <a href="{{ route('attendance.create') }}">勤怠</a>
                <a href="{{ route('attendance.index') }}">勤怠一覧</a>
                <a href="{{ route('request.index') }}">申請一覧</a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-button">ログアウト</button>
                </form>

            </div>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
</body>

</html>