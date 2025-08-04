@extends('layouts.user_app')

@section('css')
<link rel="stylesheet" href=" {{ asset('css/admin/show_approval.css') }}">
@endsection

@section('title', 'ユーザー 勤怠詳細 | 勤怠管理システム')


@section('content')
<main>
    <div class="container">
        <h2 class="page-title">勤怠詳細</h2>
        @if($pendingRequest && $pendingRequest->status === 'pending')
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
                <td class="time-cell">
                    <p class="time-start">{{ $pendingRequest->clock_in ? \Carbon\Carbon::parse($pendingRequest->clock_in)->format('H:i') : '-' }}</p>
                    <p class="wavy-dash">〜</p>
                    <p class="time-end">{{ $pendingRequest->clock_out ? \Carbon\Carbon::parse($pendingRequest->clock_out)->format('H:i') : '-' }}</p>
            </tr>
            @forelse($pendingRequest->editRequestBreaks as $index => $break)
            <tr>
                <th class="row-header">
                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                </th>
                <td class="time-cell">
                    <p class="time-start">{{ $break->break_start ? \Carbon\Carbon::parse($break->break_start)->format('H:i') : '' }}</p>
                    <p class="wavy-dash">〜</p>
                    <p class="time-end">{{ $break->break_end ? \Carbon\Carbon::parse($break->break_end)->format('H:i') : '' }}</p>
                </td>
            </tr>
            @empty
            <tr>
                <th class="row-header">休憩</th>
                <td class="time-cell"></td>
            </tr>
            @endforelse
            <tr>
                <th class="row-header">備考</th>
                <td>{{ $pendingRequest->note ?? '-' }}</td>
            </tr>
        </table>
        <p class="alert">*承認待ちのため修正できません。</p>
        @elseif($pendingRequest && $pendingRequest->status === 'approved')
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
                <td class="time-cell">
                    <p class="time-start">{{ $pendingRequest->clock_in ? \Carbon\Carbon::parse($pendingRequest->clock_in)->format('H:i') : '-' }}</p>
                    <p class="wavy-dash">〜</p>
                    <p class="time-end">{{ $pendingRequest->clock_out ? \Carbon\Carbon::parse($pendingRequest->clock_out)->format('H:i') : '-' }}</p>
            </tr>
            @forelse($pendingRequest->editRequestBreaks as $index => $break)
            <tr>
                <th class="row-header">
                    {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                </th>
                <td class="time-cell">
                    <p class="time-start">{{ $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '-' }}</p>
                    <p class="wavy-dash">〜</p>
                    <p class="time-end">{{ $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '-' }}</p>
                </td>
            </tr>
            @empty
            <tr>
                <th class="row-header">休憩</th>
                <td class="time-cell">-</td>
            </tr>
            @endforelse
            <tr>
                <th class="row-header">備考</th>
                <td>{{ $pendingRequest->note ?? '-' }}</td>
            </tr>
        </table>
        <p class="alert">*すでに承認されたため修正はできません。</p>
        @else
        <form action="{{ route('user.request.store')}}" method="POST">
            @csrf
            <table class="attendance-table">
                <tr style="display: none;">
                    <td colspan="2">
                        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
                    </td>
                </tr>
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
                            <input type="text" name="clock_in" class="time-input" value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                            <span class="tilde">〜</span>
                            <input type="text" name="clock_out" class="time-input" value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
                        </div>
                        @error('clock_in')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error('clock_out')
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @forelse ($breakTimes as $index => $break)
                <tr>
                    <th class="row-header">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </th>
                    <td class="input-cell">
                        <div class="time-range">
                            <input type="text" class="time-input" name="breaks[{{ $index }}][start]"
                                value="{{ old("breaks.$index.start", $break->break_in ? \Carbon\Carbon::parse($break->break_in)->format('H:i') : '') }}">
                            <span class="tilde">〜</span>
                            <input type="text" class="time-input" name="breaks[{{ $index }}][end]"
                                value="{{ old("breaks.$index.end", $break->break_out ? \Carbon\Carbon::parse($break->break_out)->format('H:i') : '') }}">
                        </div>
                        @error("breaks.{$index}.start")
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error("breaks.{$index}.end")
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @empty
                <tr>
                    <th class="row-header">休憩</th>
                    <td class="input-cell">
                        <div class="time-range">
                            <input type="text" class="time-input" name="breaks[0][start]" value="">
                            <span class="tilde">〜</span>
                            <input type="text" class="time-input" name="breaks[0][end]" value="">
                        </div>
                        @error("breaks.0.start")
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                        @error("breaks.0.end")
                        <div class="error-message">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
                @endforelse
                <tr>
                <tr>
                    <th class="row-header">備考</th>
                    <td colspan="2" class="input-cell">
                        <textarea class="note-area" name="note">{{ old('note', $attendance->note ?? '') }}</textarea>
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