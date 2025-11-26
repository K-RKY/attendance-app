<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $date = Carbon::now();

        return [
            'user_id' => User::factory(),
            'date' => $date->toDateString(),
            'clock_in' => $date->copy()->setTime(9, 0, 0),
            'clock_out' => $date->copy()->setTime(18, 0, 0),
            'remarks' => '',
            'status' => 0,
        ];
    }
}
