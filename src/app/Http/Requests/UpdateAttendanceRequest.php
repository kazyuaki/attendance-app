<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => 'nullable|date_format:H:i',
            'clock_out' => 'nullable|date_format:H:i',
            'break1_start' => 'nullable|date_format:H:i',
            'break1_end' => 'nullable|date_format:H:i',
            'break2_start' => 'nullable|date_format:H:i',
            'break2_end' => 'nullable|date_format:H:i',
            'note' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.date_format' => '出勤時刻は「時:分」の形式で入力してください（例: 09:00）',
            'clock_out.date_format' => '退勤時刻は「時:分」の形式で入力してください（例: 18:00）',
            'break1_start.date_format' => '休憩1の開始時刻は「時:分」の形式で入力してください',
            'break1_end.date_format' => '休憩1の終了時刻は「時:分」の形式で入力してください',
            'break2_start.date_format' => '休憩2の開始時刻は「時:分」の形式で入力してください',
            'break2_end.date_format' => '休憩2の終了時刻は「時:分」の形式で入力してください',
            'note.max' => '備考は255文字以内で入力してください',
            'note.string' => '備考は文字列で入力してください',
        ];
    }
}
