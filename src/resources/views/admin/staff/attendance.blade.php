@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="../../../css/admin/attendance-index.css">
@endsection

@section('content')
<main>
    <div class="container">
        <h2>{{ $user->name }}さんの勤怠</h2>
        @php
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');
        @endphp

        <div class="date-nav">
            <a href="{{ route('admin.users.attendances', ['user' => $user->id, 'date' => $prevMonth]) }}" class="date-nav__button">
                <img src="{{ asset('img/arrow-left.png') }}" alt="前月" class="arrow-icon"> 前月
            </a>

            <div class="date-nav__center">
                <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="calendar-icon">
                <span class="date-nav__text">{{ $currentMonth->format('Y/m') }}</span>
            </div>

            <a href="{{ route('admin.users.attendances', ['user' => $user->id, 'date' => $nextMonth]) }}" class="date-nav__button">
                翌月 <img src="{{ asset('img/arrow-right.png') }}" alt="翌月" class="arrow-icon">
            </a>
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
                    <td>{{ $attendance->breakTime_formatted ?? '--:--' }}</td>
                    <td>{{ $attendance->total_work_time ?? '--:--' }}</td>
                    <td><a href="{{ route('admin.attendances.show',['id' => $attendance->id]) }}" class="detail-link">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
@endsection