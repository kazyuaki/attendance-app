@extends('layouts.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/punch.css') }}">
@endsection

@section('title', 'ユーザー 出退勤登録 | 勤怠管理システム')


@section('content')
<main>
    <div class="container">
        <div class="punch">

            {{-- 出勤・退勤・休憩入（共通フォーム） --}}
            @if ($status === 'not_working')
            <form action="{{ route('attendance.store') }}" method="POST">
                @csrf
                <div class="punch__inner">
                    <div class="condition">勤務外</div>
                    <div class="punch__item">
                        @php
                        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                        @endphp
                        <p class="year-date">{{ $now->format('Y年n月j日') }}({{ $weekdays[$now->dayOfWeek] }})</p>
                        <h2 class="time">{{ $now->format('H:i') }}</h2>
                    </div>
                    <button type="submit" name="action" class="punch__button" value="clock_in">出勤</button>
                </div>
            </form>

            @elseif ($status === 'working')
            {{-- 出勤中：退勤＋休憩入 --}}
            <div class="punch__inner">
                <div class="condition">出勤中</div>
                <div class="punch__item">
                    @php
                    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                    @endphp
                    <p class="year-date">{{ $now->format('Y年n月j日') }}({{ $weekdays[$now->dayOfWeek] }})</p>
                    <h2 class="time">{{ $now->format('H:i') }}</h2>
                </div>
                <div class="button__group">
                    {{-- 退勤ボタン --}}
                    <form action="{{ route('attendance.store') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" name="action" class="punch__button" value="clock_out">退勤</button>
                    </form>

                    {{-- 休憩入ボタン（別ルートへ） --}}
                    <form action="{{ route('break.start') }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="break__button">休憩入</button>
                    </form>
                </div>
            </div>

            @elseif ($status === 'on_break')
            {{-- 休憩中：休憩戻ボタン --}}
            <form action="{{ route('break.end') }}" method="POST">
                @csrf
                <div class="punch__inner">
                    <div class="condition">休憩中</div>
                    <div class="punch__item">
                        @php
                        $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                        @endphp
                        <p class="year-date">{{ $now->format('Y年n月j日') }}({{ $weekdays[$now->dayOfWeek] }})</p>
                        <h2 class="time">{{ $now->format('H:i') }}</h2>
                    </div>
                    <button type="submit" class="break__button">休憩戻</button>
                </div>
            </form>

            @elseif ($status === 'clocked_out')
            {{-- 退勤済：ボタンなし --}}
            <div class="punch__inner">
                <div class="condition">退勤済</div>
                <div class="punch__item">
                    @php
                    $weekdays = ['日', '月', '火', '水', '木', '金', '土'];
                    @endphp
                    <p class="year-date">{{ $now->format('Y年n月j日') }}({{ $weekdays[$now->dayOfWeek] }})</p>
                    <h2 class="time">{{ $now->format('H:i') }}</h2>
                </div>
                <p class="message">お疲れ様でした。</p>
            </div>
            @endif

        </div>
    </div>
</main>
@endsection