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

    public function prepareForValidation()
    {
        $breaks = [];

        for ($i = 1; $i <= 5; $i++) {
            $start = $this->input("break{$i}_start");
            $end = $this->input("break{$i}_end");

            if ($start || $end) {
                $breaks[] = [
                    'start' => $start,
                    'end' => $end,
                ];
            }

            $this->merge([
                'breaks' => $breaks,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:255'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.date_format' => '出勤時刻は「HH:MM」形式で入力してください。',
            'clock_out.date_format' => '退勤時刻は「HH:MM」形式で入力してください。',
            'clock_out.after' => '退勤時刻は出勤時刻より後の時刻を入力してください。',

            'note.string' => '備考は文字列で入力してください。',
            'note.max' => '備考は255文字以内で入力してください。',

            'breaks.*.start.date_format' => '休憩開始時刻は「HH:MM」形式で入力してください。',
            'breaks.*.end.date_format' => '休憩終了時刻は「HH:MM」形式で入力してください。',
            'breaks.*.end.after' => '休憩終了時刻は休憩開始時刻より後の時刻を入力してください。',
        ];
    }


}
