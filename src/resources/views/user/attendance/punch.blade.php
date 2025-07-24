@extends('layouts.user_app')

@section('css')
<link rel="stylesheet" href="../../../css/user/punch.css">
@endsection


@section('content')
<main>
    <div class="container">
        <div class="punch">
            <form action="{{route('attendance.store')}}" method="POST">
                @csrf
                <div class="punch__inner">
                    <div class="condition">{{
                            match($status) {
                                'not_working' => '勤務外',
                                'working' => '出勤中',
                                'on_break' => '休憩中',
                                'clocked_out' => '退勤済',
                            }
                        }}
                    </div>
                    <div class="punch__item">
                        @php
                        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                        @endphp
                        <p class="year-date"> {{ $now->format('Y年n月j日') }}({{ $weekdays[$now->dayOfWeek] }})</p>
                        <h2 class="time">{{ $now->format('H:i') }}</h2>
                    </div>
                    @if ($status === 'not_working')
                    <button type="submit" name="action" class="punch__button" value="clock_in">出勤</button>
                    @elseif ($status === 'working')
                    <div class="button__group">
                        <button type="submit" name="action" class="punch__button" value="clock_out">退勤</button>
                        <button type="submit" name="action" class="break__button" value="break_start">休憩入</button>
                    </div>
                    @elseif ($status === 'on_break')
                    <button type="submit" name="action" class="break__button" value="break_end">休憩戻</button>
                    @elseif ($status === 'clocked_out')
                    <p class="message">お疲れ様でした。</p>
                    @endif
                </div>
            </form>
        </div>
    </div>
</main>
@endsection