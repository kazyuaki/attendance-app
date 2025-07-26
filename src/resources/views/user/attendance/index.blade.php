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
                <img src="../../../img/arrow-left.png" alt="矢印" class="arrow-icon">
                前月</button>
            <div class="date-nav__center">
                <img src="../../../img/calendar.png" alt="カレンダー" class="calendar-icon">
                <span class="date-nav__text">2023/06</span>
            </div>
            <button class="date-nav__button">翌月
                <img src="../../../img/arrow-right.png" alt="矢印" class="arrow-icon">
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
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($attendance->work_date)->locale('ja')->isoFormat('MM/DD(ddd)') }}</td>
                    <td>{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</td>
                    <td>{{ $attendance->break_time_formatted ?? '--:--' }}</td>
                    <td>{{ $attendance->total_work_time ?? '--:--' }}</td>
                    <td><a href="{{ route('attendance.show', ['id' => $attendance->id]) }}" class="detail-link">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
@endsection