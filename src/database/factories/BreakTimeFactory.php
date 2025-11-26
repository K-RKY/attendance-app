<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakTime;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTimeFactory extends Factory
{
    protected $model = BreakTime::class;

    public function definition()
    {
        $date = Carbon::today();
        return [
            'attendance_id' => Attendance::factory(),
            'break_start' => $date->format('Y-m-d') . ' 12:00:00',
            'break_end' => $date->format('Y-m-d') . ' 13:00:00',
        ];
    }
}
