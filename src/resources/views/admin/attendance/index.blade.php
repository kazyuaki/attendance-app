@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="../../../css/common.css">
<link rel="stylesheet" href="../../../css/admin/attendance-index.css">
@endsection

@section('content')
<main>
    <div class="container">
        <h2>勤怠一覧</h2>
        @php
        $currentDate = \Carbon\Carbon::parse($date);
        $prevDate = $currentDate->copy()->subDay()->format('Y-m-d');
        $nextDate = $currentDate->copy()->addDay()->format('Y-m-d');
        @endphp

        <div class="date-nav">
            <a href="{{ route('admin.attendances.index', ['date' => $prevDate]) }}" class="date-nav__button">
                <img src="{{ asset('img/arrow-left.png') }}" alt="前日" class="arrow-icon"> 前日
            </a>

            <div class="date-nav__center">
                <img src="{{ asset('img/calendar.png') }}" alt="カレンダー" class="calendar-icon">
                <span class="date-nav__text">{{ $currentDate->format('Y/m/d') }}</span>
            </div>

            <a href="{{ route('admin.attendances.index', ['date' => $nextDate]) }}" class="date-nav__button">
                翌日 <img src="../../../img/arrow-right.png" alt="翌日" class="arrow-icon">
            </a>
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
                        {{ gmdate('H:i', ($attendance->break_times ?? collect())->sum(function($b) {
                            return strtotime($b->break_end) - strtotime($b->break_start);
                            })) 
                        }}
                    </td>
                    <td>
                        {{ gmdate('H:i', strtotime($attendance->clock_out) - strtotime($attendance->clock_in) - $attendance->breakTimes->sum(function($b) {
                return strtotime($b->break_end) - strtotime($b->break_start);
            })) }}
                    </td>
                    <td><a href="{{ route('admin.attendances.show', $attendance->id) }}" class="detail-link">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
@endsection