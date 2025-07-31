@extends('layouts.user_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/request-index.css') }}">
@endsection

@section('title', 'ユーザー 修正一覧 | 勤怠管理システム')


@section('content')
<main>
    <div class="container">
        <h2>申請一覧</h2>
        <div class="approval-tab">
            <a href="{{ route('user.request.index', ['status' => 'pending']) }}"
                class="approval-tab__stay {{ request('status', 'pending') === 'pending' ? 'active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('user.request.index', ['status' => 'approved']) }}"
                class="approval-tab__done {{ request('status') === 'approved' ? 'active' : '' }}">
                承認済み
            </a>
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
                @forelse($requests as $request)
                <tr>
                    <td>{{ $request->status === 'pending' ? '承認待ち' : '承認済み'}}</td>
                    <td>{{ $request->user->name ?? '不明' }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->work_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->note ?? '-' }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td><a href="{{ route('attendance.show', ['id' => $request->attendance_id]) }}" class="detail-link">詳細</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6">申請はありません</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
@endsection