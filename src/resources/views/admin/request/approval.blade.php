@extends('layouts.admin_app')


@section('title', '管理者 修正申請確認画面 | 勤怠管理システム')


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
                        <p>09:00</p>
                        <span class="tilde">〜</span>
                        <p>18:00</p>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="row-header">休憩</th>
                <td class="input-cell">
                    <div class="time-range">
                        <p>12:00</p>
                        <span class="tilde">〜</span>
                        <p>13:00</p>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="row-header">休憩2</th>
                <td class="input-cell">
                    <div class="time-range">

                        <span class="tilde">〜</span>

                    </div>
                </td>
            </tr>
            <tr>
                <th class="row-header">備考</th>
                <td colspan="2" class="input-cell">
                    <p>電車遅延のため</p>
                </td>
            </tr>
        </table>
        <div class="button-wrapper">
            <button class="edit-button">承認</button>
        </div>
    </div>
</main>
@endsection