<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceEditRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date') ?? now()->toDateString();
        $attendances = Attendance::with(['user', 'breakTimes', 'attendanceEditRequests'])
            ->whereDate('work_date', $date)
            ->latest()
            ->get()
            ->map(function ($attendance) {
                $totalBreakSeconds = $attendance->breakTimes->sum(function ($break) {
                    if ($break->break_in && $break->break_out) {
                        return strtotime($break->break_out) - strtotime($break->break_in);
                    }
                    return 0;
                });

                $clockIn = $attendance->clock_in ? strtotime($attendance->clock_in) : null;
                $clockOut = $attendance->clock_out ? strtotime($attendance->clock_out) : null;

                $workingSeconds = ($clockIn && $clockOut)
                    ? max(0, $clockOut - $clockIn - $totalBreakSeconds)
                    : 0;

                // Bladeで直接使えるように追加
                $attendance->total_break = gmdate('H:i', $totalBreakSeconds);
                $attendance->working_time = gmdate('H:i', $workingSeconds);

                return $attendance;
            });
        return view('admin.attendance.index', compact('attendances', 'date'));
    }

    // 詳細表示（管理者側）
    public function show($id, Request $request)
    {

        $attendance = Attendance::with('user', 'breakTimes')->findOrFail($id);

        $pendingRequest = AttendanceEditRequest::where('attendance_id', $id)
            ->where('status', 'pending')
            ->first();

        $breaks = $attendance->breakTimes;
        return view('admin.attendance.show', compact('attendance',  'pendingRequest'));
    }

    //管理者 勤怠詳細修正機能
    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);
        $date = $attendance->work_date;
        $validated = $request->validated();

        // 勤怠本体
        $attendance->clock_in = $validated['clock_in'] ? Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['clock_in']}") : null;
        $attendance->clock_out = $validated['clock_out'] ? Carbon::createFromFormat('Y-m-d H:i', "$date {$validated['clock_out']}") : null;
        $attendance->note = $validated['note'] ?? null;
        $attendance->save();

        // 既存の休憩を取得（上書き・削除のために）
        $existingBreaks = $attendance->breakTimes;

        // 入力された休憩を保存・更新
        $newBreaks = $validated['breaks'] ?? [];

        foreach ($newBreaks as $index => $breakInput) {
            $start = !empty($breakInput['start']) ? Carbon::createFromFormat('Y-m-d H:i', "$date {$breakInput['start']}") : null;
            $end = !empty($breakInput['end']) ? Carbon::createFromFormat('Y-m-d H:i', "$date {$breakInput['end']}") : null;

            // 両方とも空ならスキップ
            if (!$start && !$end) {
                continue;
            }

            $existing = $existingBreaks->get($index);
            if ($existing) {
                $existing->break_in = $start;
                $existing->break_out = $end;
                $existing->save();
            } else {
                $attendance->breakTimes()->create([
                    'break_in' => $start,
                    'break_out' => $end,
                ]);
            }
        }

        // 余分な休憩（削除）
        if (count($existingBreaks) > count($newBreaks)) {
            for ($i = count($newBreaks); $i < count($existingBreaks); $i++) {
                $existingBreaks[$i]->delete();
            }
        }

        return redirect()->route('admin.attendances.index')
            ->with('success', '勤怠データを更新しました。');
    }

    public function exportCsv(Request $request, User $user): StreamedResponse
    {
        $date = trim($request->input('date') ?? now()->format('Y-m'));
        $carbonDate = Carbon::parse($date)->startOfMonth();

        $attendances = Attendance::with('breakTimes')
            ->where('user_id', $user->id)
            ->whereYear('work_date', $carbonDate->year)
            ->whereMonth('work_date', $carbonDate->month)
            ->orderBy('work_date', 'asc')
            ->get();

        $filename = $user->name . "_attendance_" . $carbonDate->format('Y_m') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($attendances) {
            $stream = fopen('php://output', 'w');
            // ヘッダー行
            fputcsv($stream, ['日付', '出勤', '退勤', '休憩時間', '勤務時間', '備考']);

            foreach ($attendances as $a) {
                $breakMinutes = $a->breakTimes->sum(function ($b) {
                    if ($b->break_in && $b->break_out) {
                        return Carbon::parse($b->break_out)->diffInMinutes(Carbon::parse($b->break_in));
                    }
                    return 0;
                });

                $totalWork = '--:--';
                if ($a->clock_in && $a->clock_out) {
                    $workMinutes = Carbon::parse($a->clock_out)->diffInMinutes(Carbon::parse($a->clock_in));
                    $netMinutes = max($workMinutes - $breakMinutes, 0);
                    $totalWork = sprintf('%02d:%02d', floor($netMinutes / 60), $netMinutes % 60);
                }

                fputcsv($stream, [
                    $a->work_date,
                    $a->clock_in,
                    $a->clock_out,
                    sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60),
                    $totalWork,
                    $a->note ?? '',
                ]);
            }

            fclose($stream);
        };

        return response()->stream($callback, 200, $headers);
    }
}
