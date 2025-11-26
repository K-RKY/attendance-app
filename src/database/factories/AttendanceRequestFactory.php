<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Illuminate\Support\Carbon;

class AttendanceRequestFactory extends Factory
{
    protected $model = AttendanceRequest::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = Carbon::now();

        return [
            'attendance_id' => Attendance::factory(),
            'requested_date' => $date->toDateString(),
            'requested_clock_in' => $date->copy()->setTime(9, 0, 0),
            'requested_clock_out' => $date->copy()->setTime(18, 0, 0),
            'requested_remarks' => '',
            'status' => 0,
        ];
    }
}
