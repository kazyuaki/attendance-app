@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/show_approval.css') }}">
@endsection

@section('title', '管理者勤怠詳細| 勤怠管理システム')


@section('content')
<main>
    <div class="container">
        <h2 class="page-title">勤怠詳細</h2>
        @if($pendingRequest)
        <table class="attendance-table">
            <tr>
                <th class="row-header">名前</th>
                <td colspan="2">{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th class="row-header">日付</th>
                <td colspan="2">{{ \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') }}</td>
            </tr>
            <tr>
                <th class="row-header">申請出勤・退勤</th>
                <td>
                    {{ $pendingRequest->clock_in ? \Carbon\Carbon::parse($pendingRequest->clock_in)->format('H:i') : '-' }}
                    〜
                    {{ $pendingRequest->clock_out ? \Carbon\Carbon::parse($pendingRequest->clock_out)->format('H:i') : '-' }}
                </td>
            </tr>
            <tr>
                <th class="row-header">休憩</th>
                <td>
                    @forelse($pendingRequest->editRequestBreaks as $index => $break)
                    {{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '-' }}
                    〜
                    {{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '-' }}
                    @if (!$loop->last)<br>@endif
                    @empty
                    -
                    @endforelse
                </td>
            </tr>
            <tr>
                <th class="row-header">備考</th>
                <td>{{ $pendingRequest->note ?? '-' }}</td>
            </tr>
        </table>
        <div class="info-message">この勤怠データには承認待ちの修正申請があります。編集はできません。</div>
        @else
        <form action="{{ route('admin.attendances.update', $attendance->id) }}" method="POST">
            @csrf
            @method('PATCH')
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
                            <input type="text" name="clock_in" class="time-input"
                                value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}"> <span class="tilde">〜</span>
                            <input type="text" name="clock_out" class="time-input"
                                value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                        </div>
                        @error('clock_in')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error('clock_out')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @forelse($attendance->breakTimes->sortBy('break_number') as $index => $break)
                <tr>
                    <th class="row-header">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </th>
                    <td class="input-cell">
                        <div class="time-range">
                            <input type="text" name="break{{ $index + 1 }}_start" class="time-input"
                                value="{{ old('break{$index + 1}_start', $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '') }}">
                            <span class="tilde">〜</span>
                            <input type="text" name="break{{ $index + 1 }}_end" class="time-input"
                                value="{{ old('break{$index + 1}_end', $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '') }}">
                        </div>
                        @error('break{$index + 1}_start')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error('break{$index + 1}_end')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @empty
                <tr>
                    <th class="row-header">休憩</th>
                    <td class="input-cell">
                        <div class="time-range">
                            <input type="text" name="break_start" class="time-input" value="">
                            <span class="tilde">〜</span>
                            <input type="text" name="break_end" class="time-input" value="">
                        </div>
                    </td>
                </tr>
                @endforelse
                <tr>
                    <th class="row-header">備考</th>
                    <td colspan="2" class="input-cell">
                        <textarea class="note-area" name="note">{{ old('note', $attendance->note) }}</textarea>
                        @error('note')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>
            <div class="button-wrapper">
                <button class="edit-button">修正</button>
            </div>
        </form>
        @endif
    </div>
</main>
@endsection