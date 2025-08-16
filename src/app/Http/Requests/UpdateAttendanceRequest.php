<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

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
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'note' => ['required', 'string'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            // 出退勤の順序チェック（片方だけにエラーをつける）
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩時間の妥当性チェック（breaks 配列に対応）
            $breaks = $this->input('breaks', []);
            foreach ($breaks as $index => $break) {
                $breakStart = $break['start'] ?? null;
                $breakEnd = $break['end'] ?? null;

                if ($breakStart) {
                    if ($clockIn && $breakStart < $clockIn) {
                        $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                    }
                    if ($clockOut && $breakStart > $clockOut) {
                        $validator->errors()->add("breaks.$index.start", '休憩時間が不適切な値です');
                    }
                }

                if ($breakEnd && $clockOut && $breakEnd > $clockOut) {
                    $validator->errors()->add("breaks.$index.end", '休憩時間もしくは退勤時間が不適切な値です');
                }
            }

            // 備考欄のチェック（ルールで済むが念のため再確認）
            if (empty(trim($this->input('note')))) {
                $validator->errors()->add('note', '備考を記入してください');
            }
        });
    }

    public function messages()
    {
        return [
            'breaks.*.start.date_format' => '休憩開始時間は H:i 形式で入力してください。',
            'breaks.*.end.date_format'   => '休憩終了時間は H:i 形式で入力してください。',
            'note.required' => '備考を記入してください',
            'clock_in.required' => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
        ];
    }
}
