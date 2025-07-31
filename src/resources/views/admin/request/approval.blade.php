@extends('layouts.admin_app')


@section('title', '管理者 修正申請確認画面 | 勤怠管理システム')


@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/show_approval.css') }}">
@endsection

@section('content')
<main>
    <div class="container">
        <h2 class="page-title">勤怠詳細</h2>
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
                    <th class="row-header">申請出勤・退勤</th>
                    <td class="time-cell">
                        <p class="time-start"> {{ $pendingRequest->clock_in ? \Carbon\Carbon::parse($pendingRequest->clock_in)->format('H:i') : '-' }}</p>
                        <p class="wavy-dash">〜</p>
                        <p class="time-end">{{ $pendingRequest->clock_out ? \Carbon\Carbon::parse($pendingRequest->clock_out)->format('H:i') : '-' }}</p>
                    </td>
                </tr>
                <tr>
                    <th class="row-header">休憩</th>
                    <td class="time-cell">
                        <p class="time-start"> {{ $pendingRequest->break1_start ? \Carbon\Carbon::parse($pendingRequest->break1_start)->format('H:i') : '-' }}</p>
                        <p class="wavy-dash">〜</p>
                        <p class="time-end">{{ $pendingRequest->break1_end ? \Carbon\Carbon::parse($pendingRequest->break1_end)->format('H:i') : '-' }}</p>
                    </td>
                </tr>
                <tr>
                    <th class="row-header">休憩2</th>
                    <td class="time-cell">
                        <p class="time-start"> {{ $pendingRequest->break2_start ? \Carbon\Carbon::parse($pendingRequest->break2_start)->format('H:i') : '-' }}</p>
                        <p class="wavy-dash">〜</p>
                        <p class="time-end">{{ $pendingRequest->break2_end ? \Carbon\Carbon::parse($pendingRequest->break2_end)->format('H:i') : '-' }}</p>
                    </td>
                </tr>
                <tr>
                    <th class="row-header">備考</th>
                    <td>{{ $pendingRequest->note ?? '-' }}</td>
                </tr>
            </table>
            <div class="button-wrapper">
                <button class="edit-button" type="submit">承認</button>
            </div>
        </form>
    </div>
</main>
@endsection