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
                    <td colspan="2">西 怜奈</td>
                </tr>
                <tr>
                    <th class="row-header">日付</th>
                    <td colspan="2" class="date-cell">
                        <span class="year">2023年</span>
                        <span class="day">6月1日</span>
                    </td>
                </tr>
                <tr>
                    <th class="row-header">出勤・退勤</th>
                    <td class="input-cell">
                        <div class="time-range">
                            <input type="text" class="time-input">
                            <span class="tilde">〜</span>
                            <input type="text" class="time-input">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="row-header">休憩</th>
                    <td class="input-cell">
                        <div class="time-range">
                            <input type="text" class="time-input">
                            <span class="tilde">〜</span>
                            <input type="text" class="time-input">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="row-header">休憩2</th>
                    <td class="input-cell">
                        <div class="time-range">
                            <input type="text" class="time-input">
                            <span class="tilde">〜</span>
                            <input type="text" class="time-input">
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