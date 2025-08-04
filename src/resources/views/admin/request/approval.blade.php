@extends('layouts.admin_app')


@section('title', '管理者 修正申請確認画面 | 勤怠管理システム')


@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/show_approval.css') }}">
@endsection

@section('content')
<main>
    <div class="container">
        <h2 class="page-title">勤怠詳細</h2>
        @if($pendingRequest && $pendingRequest->status === 'pending')
         <form action="{{ route('admin.requests.approve', $pendingRequest->id) }}" method="POST">
            @csrf
            <table class="attendance-table">
                <tr>
                    <th class="row-header">名前</th>
                    <td colspan="2">{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th class="row-header">日付</th>
                    <td colspan="2" class="date-cell">
                        <span class="year">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                        <span class="day">{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                    </td>
                </tr>
                <tr>
                    <th class="row-header">出勤・退勤</th>
                    <td colspan="2" class="time-range">
                        <p class="time-input">{{ \Carbon\Carbon::parse($pendingRequest->clock_in)->format('H:i') ?? '-' }}</p>〜
                        <p class="time-input">{{ \Carbon\Carbon::parse($pendingRequest->clock_out)->format('H:i') ?? '-' }}</p>
                    </td>
                </tr>

                @forelse($pendingRequest->editRequestBreaks as $index => $break)
                <tr>
                    <th class="row-header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                    <td colspan="2" class="time-range">
                        <p class="time-input"> {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') ?? '-' }}</p>
                        〜
                        <p class="time-input">{{ \Carbon\Carbon::parse($break->break_end)->format('H:i') ?? '-' }}</p>
                    </td>
                </tr>
                @empty
                <tr>
                    <th class="row-header">休憩</th>
                    <td colspan="2">-</td>
                </tr>
                @endforelse

                <tr>
                    <th class="row-header">備考</th>
                    <td colspan="2">{{ $pendingRequest->note ?? '-' }}</td>
                </tr>
            </table>
            <div class="button-wrapper">
                <button class="edit-button" type="submit">承認</button>
            </div>
        </form>
        @else
        <table class="attendance-table">
            <tr>
                <th class="row-header">名前</th>
                <td colspan="2">{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th class="row-header">日付</th>
                <td colspan="2" class="date-cell">
                    <span class="year">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年') }}</span>
                    <span class="day">{{ \Carbon\Carbon::parse($attendance->work_date)->format('n月j日') }}</span>
                </td>
            </tr>
            <tr>
                <th class="row-header">出勤・退勤</th>
                <td colspan="2" class="time-range">
                    <p class="time-input">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '-' }}</p>〜
                    <p class="time-input">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '-' }}</p>
                </td>
            </tr>

            @forelse($attendance->breakTimes as $index => $break)
            <tr>
                <th class="row-header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</th>
                <td colspan="2" class="time-range">
                    <p class="time-input">{{ $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '-' }}</p>〜
                    <p class="time-input">{{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '-' }}</p>
                </td>
            </tr>
            @empty
            <tr>
                <th class="row-header">休憩</th>
                <td colspan="2">-</td>
            </tr>
            @endforelse

            <tr>
                <th class="row-header">備考</th>
                <td colspan="2">{{ $attendance->note ?? '-' }}</td>
            </tr>
        </table>
        <div class="button-wrapper">
            <button class="approved-button" type="button" disabled>承認済</button>
        </div>
        @endif
    </div>
</main>
@endsection