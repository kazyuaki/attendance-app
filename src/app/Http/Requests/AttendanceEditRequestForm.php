<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceEditRequestForm extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'attendance_id' => ['required', 'exists:attendances,id'],

            'clock_in' => ['nullable', 'required_with:clock_out', 'date_format:H:i'],
            'clock_out' => ['nullable', 'required_with:clock_in', 'date_format:H:i', 'after_or_equal:clock_in'],

            'break1_start' => ['nullable', 'required_with:break1_end', 'date_format:H:i'],
            'break1_end' => ['nullable', 'required_with:break1_start', 'date_format:H:i', 'after_or_equal:break1_start'],

            'break2_start' => ['nullable', 'required_with:break2_end', 'date_format:H:i'],
            'break2_end' => ['nullable', 'required_with:break2_start', 'date_format:H:i', 'after_or_equal:break2_start'],

            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'attendance_id.required' => '勤怠データが見つかりません。',
            'attendance_id.exists' => '対象の勤怠データが存在しません。',

            'clock_in.required_with' => '出勤時刻と退勤時刻はセットで入力してください。',
            'clock_in.date_format' => '出勤時刻は「時:分」の形式で入力してください。',
            'clock_out.required_with' => '退勤時刻と出勤時刻はセットで入力してください。',
            'clock_out.date_format' => '退勤時刻は「時:分」の形式で入力してください。',
            'clock_out.after_or_equal' => '退勤時刻は出勤時刻と同じかそれ以降にしてください。',

            'break1_start.required_with' => '休憩1の時刻は開始・終了セットで入力してください。',
            'break1_start.date_format' => '休憩1の開始時刻は「時:分」の形式で入力してください。',
            'break1_end.required_with' => '休憩1の時刻は開始・終了セットで入力してください。',
            'break1_end.date_format' => '休憩1の終了時刻は「時:分」の形式で入力してください。',
            'break1_end.after_or_equal' => '休憩1の終了時刻は開始時刻と同じかそれ以降にしてください。',

            'break2_start.required_with' => '休憩2の時刻は開始・終了セットで入力してください。',
            'break2_start.date_format' => '休憩2の開始時刻は「時:分」の形式で入力してください。',
            'break2_end.required_with' => '休憩2の時刻は開始・終了セットで入力してください。',
            'break2_end.date_format' => '休憩2の終了時刻は「時:分」の形式で入力してください。',
            'break2_end.after_or_equal' => '休憩2の終了時刻は開始時刻と同じかそれ以降にしてください。',

            'note.string' => '備考は文字列で入力してください。',
            'note.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
