<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面の「名前」がログインユーザーの氏名になっている()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-25',
            'clock_in' => '2025-11-25 09:00:00',
            'clock_out' => '2025-11-25 18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));

        $response->assertSee('John Doe');
    }

    /** @test */
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-25',
            'clock_in' => '2025-11-25 09:00:00',
            'clock_out' => '2025-11-25 18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));

        $response->assertSee('2025-11-25');
    }

    /** @test */
    public function 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-25',
            'clock_in' => '2025-11-25 09:00:00',
            'clock_out' => '2025-11-25 18:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {

        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-25',
            'clock_in' => '2025-11-25 09:00:00',
            'clock_out' => '2025-11-25 18:00:00',
        ]);

        $break = BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => '2025-11-25 12:00:00',
            'break_end' => '2025-11-25 12:30:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));

        $response->assertSeeInOrder([
            '12:00',
            '12:30',
        ]);
    }
}
