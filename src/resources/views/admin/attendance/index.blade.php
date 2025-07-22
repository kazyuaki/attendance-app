@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="../../../css/common.css">
<link rel="stylesheet" href="../../../css/admin/attendance-index.css">
@endsection

@section('content')
<main>
    <div class="container">
        <h2>勤怠一覧</h2>
        <div class="date-nav">
            <button class="date-nav__button">
                <img src="../../../img/arrow-left.png" alt="矢印" class="arrow-icon">
                前日</button>
            <div class="date-nav__center">
                <img src="../../../img/calendar.png" alt="カレンダー" class="calendar-icon">
                <span class="date-nav__text">2023/06/01</span>
            </div>
            <button class="date-nav__button">翌日
                <img src="../../../img/arrow-right.png" alt="矢印" class="arrow-icon">
            </button>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</td>
                    <td>
                        {{ gmdate('H:i', $attendance->break_times->sum(function($b) {
                            return strtotime($b->break_end) - strtotime($b->break_start);
                        })) }}
                    </td>
                        {{ gmdate('H:i', strtotime($attendance->clock_out) - strtotime($attendance->clock_in) - $attendance->breaks->sum(function($b) {
                            return strtotime($b->break_end) - strtotime($b->break_start);
                        })) }}
                    <td><a href="{{ route('admin.attendances.show', $attendance->id) }}" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>山田 太郎</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>田中 花子</td>
                    <td>08:30</td>
                    <td>17:30</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <!-- 他のスタッフのデータも同様に追加 -->
            </tbody>
        </table>
    </div>
</main>
@endsection