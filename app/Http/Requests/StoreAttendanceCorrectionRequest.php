<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
{
    
    return [
    'requested_work_date'      => ['nullable', 'date'],

    'requested_clock_in_time'  => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
    'requested_clock_out_time' => ['nullable', 'regex:/^\d{1,2}:\d{2}$/', 'after:requested_clock_in_time'],

    'requested_break_start_time' => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
    'requested_break_end_time'   => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],

    'requested_note'           => ['required', 'string', 'max:2000'],

    // 'breaks'         => ['nullable', 'array'],
    'breaks' => ['present', 'array'],
    'breaks.*.start' => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
    'breaks.*.end'   => ['nullable', 'regex:/^\d{1,2}:\d{2}$/'],
];
}
    public function messages(): array
{
    return [


        // 出退勤
        'requested_clock_in_time.required'  => '出勤時間を入力してください',
        'requested_clock_in_time.regex'     => '出勤時間が不適切な値です',
        'requested_clock_in_time.date_format'=> '出勤時間が不適切な値です',

        'requested_clock_out_time.required' => '退勤時間を入力してください',
        'requested_clock_out_time.regex'    => '退勤時間が不適切な値です',
        'requested_clock_out_time.date_format'=> '退勤時間が不適切な値です',

        // 単発休憩
        'requested_break_start_time.regex' => '休憩時間が不適切な値です',
        'requested_break_end_time.regex'   => '休憩時間が不適切な値です',

        // 配列休憩
        'breaks.*.start.regex' => '休憩時間が不適切な値です',
        'breaks.*.end.regex'   => '休憩時間が不適切な値です',

        // 備考
        'requested_note.required' => '備考を記入してください',

      
        // 'requested_clock_in_time.required'  => '出勤時間を入力してください',
        // 'requested_clock_in_time.date_format'  => '出勤時間が不適切な値です',

        // 'requested_clock_out_time.required' => '退勤時間を入力してください',
        // 'requested_clock_out_time.date_format' => '退勤時間が不適切な値です',

      
        // 'requested_break_start_time.date_format' => '休憩時間が不適切な値です',
        // 'requested_break_end_time.date_format'   => '休憩時間が不適切な値です',

        // 'breaks.*.start.date_format' => '休憩時間が不適切な値です',
        // 'breaks.*.end.date_format'   => '休憩時間が不適切な値です',

        // 'requested_note.required' => '備考を記入してください',
    ];
}

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {

            $clockIn  = $this->input('requested_clock_in_time');
            $clockOut = $this->input('requested_clock_out_time');

            // 出勤・退勤が両方ある時だけ、時間関係のバリデーションをする
            if ($clockIn && $clockOut) {

                $in  = strtotime($clockIn);
                $out = strtotime($clockOut);

                // FN039①：出勤 >= 退勤
                if ($in >= $out) {
                    $validator->errors()->add('requested_clock_in_time', '出勤時間が不適切な値です');
                    return;
                }

                // breaksを「入力があるものだけ」に寄せてチェック
                $breaks = collect($this->input('breaks', []))
                    ->map(fn ($b) => [
                        'start' => $b['start'] ?? null,
                        'end'   => $b['end'] ?? null,
                    ])
                    ->filter(fn ($b) => !empty($b['start']) || !empty($b['end']))
                    ->values();

                foreach ($breaks as $b) {
                    $start = $b['start'] ? strtotime($b['start']) : null;
                    $end   = $b['end'] ? strtotime($b['end']) : null;

                    // FN039②：休憩開始が勤務時間外（start < 出勤 OR start > 退勤）
                    if ($start !== null && ($start < $in || $start > $out)) {
                        $validator->errors()->add('breaks', '休憩時間が不適切な値です');
                        return;
                    }

                    // FN039③：休憩終了が退勤より後
                    if ($end !== null && $end > $out) {
                        $validator->errors()->add('breaks', '休憩時間もしくは退勤時間が不適切な値です');
                        return;
                    }

                    // 休憩終了 < 休憩開始
                    if ($start !== null && $end !== null && $end < $start) {
                        $validator->errors()->add('breaks', '休憩時間が不適切な値です');
                        return;
                    }
                }
            }
        });
    }
}
