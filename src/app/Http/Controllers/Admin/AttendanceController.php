<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\AttendanceEditRequest;
use App\Http\Requests\UpdateAttendanceRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $dateParam = $request->input('date');
        $date = $dateParam
            ? Carbon::parse($dateParam)->toDateString()  
            : now()->toDateString();
        $attendances = Attendance::with(['user', 'breakTimes', 'attendanceEditRequests'])
            ->whereDate('work_date', $date)
            ->orderByDesc('id')
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

    // 更新処理（管理者側）
    public function update(UpdateAttendanceRequest $request, $id)
    {
        $attendance = Attendance::with('breakTimes')->findOrFail($id);

        $workDateString = $attendance->work_date instanceof \Carbon\Carbon
            ? $attendance->work_date->format('Y-m-d')
            : \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d');

        $validated = $request->validated();

        Log::info('Attendance update start', [
            'attendance_id' => $attendance->id,
            'payload'       => $validated,
        ]);

        DB::transaction(function () use ($attendance, $workDateString, $validated) {
            // 出退勤（HH:ii を Y-m-d H:i に）
            $attendance->clock_in  = !empty($validated['clock_in'])
                ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$workDateString} {$validated['clock_in']}")
                : null;

            $attendance->clock_out = !empty($validated['clock_out'])
                ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$workDateString} {$validated['clock_out']}")
                : null;

            $attendance->note = $validated['note'] ?? null;
            $attendance->save();

            // 休憩（配列 breaks をそのまま反映：行数が変わっても安定）
            $attendance->breakTimes()->delete();
            foreach ($validated['breaks'] ?? [] as $row) {
                $start = !empty($row['start'])
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$workDateString} {$row['start']}")
                    : null;
                $end   = !empty($row['end'])
                    ? \Carbon\Carbon::createFromFormat('Y-m-d H:i', "{$workDateString} {$row['end']}")
                    : null;

                // 両方とも空ならスキップ
                if (!$start && !$end) {
                    continue;
                }

                $attendance->breakTimes()->create([
                    'break_in'  => $start,
                    'break_out' => $end,
                ]);
            }
        });

        Log::info('Attendance updated', [
            'attendance_id' => $attendance->id,
            'work_date'     => $workDateString,
            'clock_in'      => optional($attendance->clock_in)->toDateTimeString(),
            'clock_out'     => optional($attendance->clock_out)->toDateTimeString(),
            'note'          => $attendance->note,
            'breaks_now'    => $attendance->breakTimes()->get(['break_in', 'break_out'])->toArray(),
        ]);

        return redirect()
            ->route('admin.attendances.index', ['date' => $workDateString])
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
