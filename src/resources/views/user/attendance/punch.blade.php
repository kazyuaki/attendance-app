@extends('layouts.user_app')

@section('css')
    <link rel="stylesheet" href="../../../css/user/punch.css">
@endsection


@section('content')
    <main>
        <div class="container">
            <div class="punch">
                <form action="" method="post">
                    <div class="punch__inner">
                        <div class="condition">勤務外</div>
                        <div class="punch__item">
                            <p class="year-date">2023年6月1日(木)</p>
                            <h2 class="time">08:00</h2>
                        </div>
                        <button type="submit" class="punch__button">出勤</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection