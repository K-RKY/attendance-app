<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\AttendanceRequestBreak;
use Carbon\Carbon;

class AttendanceRequestBreakFactory extends Factory
{
    protected $model = AttendanceRequestBreak::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = Carbon::now();

        return [
            'attendance_request_id' => Attendance::factory(),
            'break_start' => $date->format('Y-m-d') . ' 12:00:00',
            'break_end' => $date->format('Y-m-d') . ' 13:00:00',
        ];
    }
}
