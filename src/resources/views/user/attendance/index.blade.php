@extends('layouts.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance-index.css') }}">
@endsection


@section('content')
<main>
    <div class="container">
        <h2>勤怠一覧</h2>
        <div class="date-nav">
            <button class="date-nav__button">
                <img src="../../../img/arow-left.png" alt="矢印" class="arrow-icon">
                前月</button>
            <div class="date-nav__center">
                <img src="../../../img/calendar.png" alt="カレンダー" class="calendar-icon">
                <span class="date-nav__text">2023/06</span>
            </div>
            <button class="date-nav__button">翌月
                <img src="../../../img/arow-right.png" alt="矢印" class="arrow-icon">
            </button>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>06/01(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>06/02(金)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</main>
@endsection