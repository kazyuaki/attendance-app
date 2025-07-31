@extends('layouts.admin_app')
@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/staff-index.css') }}">
@endsection

@section('title', '管理者 スタッフ一覧 | 勤怠管理システム')


@section('content')
<main>
    <div class="container">
        <h2>スタッフ一覧</h2>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="{{ route('admin.users.attendances', $user->id) }}" class="detail-link">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</main>
@endsection