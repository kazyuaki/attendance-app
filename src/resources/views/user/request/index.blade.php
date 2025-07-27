@extends('layouts.user_app')

@section('css')
<link rel="stylesheet" href="../../../css/admin/request-index.css">
@endsection

@section('content')
<main>
    <div class="container">
        <h2>申請一覧</h2>
        <div class="approval-tab">
            <div class="approval-tab__stay active">承認待ち</div>
            <div class="approval-tab__done">承認済み</div>
        </div>
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>承認待ち</td>
                    <td>西 怜奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>西 怜奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <td><a href="#" class="detail-link">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</main>
@endsection