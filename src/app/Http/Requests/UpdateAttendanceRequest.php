<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date'          => ['required'],
            'clock_in'      => ['nullable', 'date_format:H:i'],
            'clock_out'     => ['nullable', 'date_format:H:i'],
            'break_start'   => ['array'],
            'break_start.*' => ['nullable', 'date_format:H:i'],
            'break_end'     => ['array'],
            'break_end.*'   => ['nullable', 'date_format:H:i'],
            'remarks'       => ['required'],
        ];
    }

    public function messages()
    {
        return [
            'remarks.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $date = Carbon::parse($this->date)->toDateString();

            // 出勤・退勤
            $clockIn  = $this->clock_in  ? Carbon::parse("$date {$this->clock_in}:00")  : null;
            $clockOut = $this->clock_out ? Carbon::parse("$date {$this->clock_out}:00") : null;

            // 出勤 > 退勤
            if ($clockIn && $clockOut && $clockIn->gt($clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは<br>退勤時間が不適切な値です');
            }


            // 休憩は複数
            $breakStarts = $this->break_start ?? [];
            $breakEnds   = $this->break_end   ?? [];

            foreach ($breakStarts as $i => $start) {

                if (!$start) continue;

                $startTime = Carbon::parse("$date {$start}:00");

                // 出勤より前
                if ($clockIn && $startTime->lt($clockIn)) {
                    $validator->errors()->add("break_start.$i", "休憩時間が不適切な値です");
                }

                // 退勤より後
                if ($clockOut && $startTime->gt($clockOut)) {
                    $validator->errors()->add("break_start.$i", "休憩時間が不適切な値です");
                }
            }

            foreach ($breakEnds as $i => $end) {

                if (!$end) continue;

                $endTime = Carbon::parse("$date {$end}:00");

                // 休憩終了 > 退勤
                if ($clockOut && $endTime->gt($clockOut)) {
                    $validator->errors()->add("break_end.$i", "休憩時間もしくは<br>退勤時間が不適切な値です");
                }
            }
        });
    }
}
