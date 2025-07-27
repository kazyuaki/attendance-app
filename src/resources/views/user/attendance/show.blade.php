@extends('layouts.user_app')

@section('css')
<link rel="stylesheet" href="../../../css/admin/show_approval.css">
@endsection

@section('content')

<main>
    <div class="container">
        <h2 class="page-title">勤怠詳細</h2>

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
                <td class="input-cell">
                    <div class="time-range">
                        <input type="text" class="time-input" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                        <span class="tilde">〜</span>
                        <input type="text" class="time-input" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                    </div>
                </td>
            </tr>
            <tr>
                <th class="row-header">休憩</th>
                <td class="input-cell">
                    <div class="time-range">
                        <input type="text" name="break_start" class="time-input" value="{{ optional(optional($attendance->break1)->break_start)->format('H:i') }}">
                        <span class="tilde">〜</span>
                        <input type="text" name="break_end" class="time-input"  value="{{ optional(optional($attendance->break1)->break_end)->format('H:i') }}">
                    </div>
                </td>
            </tr>
            <tr>
                <th class="row-header">休憩2</th>
                <td class="input-cell">
                    <div class="time-range">
                        <input type="text" class="time-input" value="{{ optional(optional($attendance->break2)->break_start)->format('H:i') }}">
                        <span class="tilde">〜</span>
                        <input type="text" class="time-input" value="{{ optional(optional($attendance->break2)->break_end)->format('H:i') }}">
                    </div>
                </td>
            </tr>
            <tr>
                <th class="row-header">備考</th>
                <td colspan="2" class="input-cell">
                    <textarea class="note-area"></textarea>
                </td>
            </tr>
        </table>

        <div class="button-wrapper">
            <button class="edit-button">修正</button>
        </div>
    </div>
</main>

@endsection