<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;

class UsersAndAttendancesSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者ユーザー作成
        $admin = User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        // 一般ユーザー5名作成
        $generalUsers = User::factory()->count(5)->create();

        $allUsers = $generalUsers->push($admin);

        foreach ($allUsers as $user) {
            // 過去5日間の勤怠
            for ($i = 0; $i <= 5; $i++) {
                $date = Carbon::now()->subDays($i)->toDateString();

                $clockInHour = rand(9, 10);
                $clockInMinute = rand(0, 59);
                $clockOutHour = rand(17, 19);
                $clockOutMinute = rand(0, 59);

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'clock_in' => "$date $clockInHour:$clockInMinute:00",
                    'clock_out' => "$date $clockOutHour:$clockOutMinute:00",
                    'remarks' => '',
                    'status' => 3, // 退勤済
                ]);

                // 休憩1〜2件をランダム作成
                $numBreaks = rand(1, 2);
                for ($j = 0; $j < $numBreaks; $j++) {
                    $breakStartHour = rand(12, 13);
                    $breakStartMinute = rand(0, 30);
                    $breakEndHour = $breakStartHour;
                    $breakEndMinute = $breakStartMinute + rand(15, 60);
                    if ($breakEndMinute >= 60) {
                        $breakEndMinute -= 60;
                        $breakEndHour += 1;
                    }

                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'break_start' => "$date $breakStartHour:$breakStartMinute:00",
                        'break_end' => "$date $breakEndHour:$breakEndMinute:00",
                    ]);
                }
            }
        }
    }
}
