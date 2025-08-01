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

            'breaks.*.start' => ['nullable', 'required_with:breaks.*.end', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'required_with:breaks.*.start', 'date_format:H:i', 'after_or_equal:breaks.*.start'],

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

            'breaks.*.start.required_with' => '休憩時間は開始・終了セットで入力してください。',
            'breaks.*.start.date_format' => '休憩開始時刻は「時:分」の形式で入力してください。',
            'breaks.*.end.required_with' => '休憩時間は開始・終了セットで入力してください。',
            'breaks.*.end.date_format' => '休憩終了時刻は「時:分」の形式で入力してください。',
            'breaks.*.end.after_or_equal' => '休憩終了時刻は開始時刻と同じかそれ以降にしてください。',

            'note.string' => '備考は文字列で入力してください。',
            'note.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
