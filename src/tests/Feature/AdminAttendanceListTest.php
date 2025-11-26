<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $admin = User::factory()->create(['role' => 1]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'date' => '2025-11-01',
            'clock_in' => '2025-11-01 09:00:00',
            'clock_out' => '2025-11-01 18:00:00',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'date' => '2025-11-01',
            'clock_in' => '2025-11-01 09:00:00',
            'clock_out' => '2025-11-01 18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=2025-11-01');

        $response->assertStatus(200);

        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
        $response->assertSee('2025/11/01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }


    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        $admin = User::factory()->create(['role' => 1]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $currentDate = Carbon::now()->format('Y/m/d');

        $response->assertStatus(200);

        $response->assertSeeText($currentDate);
    }

    /** @test */
    public function 「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role' => 1]);

        $prevDay = Carbon::now()->subDay();

        $attendancePrev = Attendance::factory()->create([
            'user_id' => $admin->id,
            'date' => $prevDay->toDateString(),
            'clock_in' => $prevDay->copy()->setTime(9, 0)->toDateTimeString(),
            'clock_out' => $prevDay->copy()->setTime(18, 0)->toDateTimeString(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $prevDay->toDateString());

        $response->assertStatus(200);

        $response->assertSeeText($attendancePrev->date->format('m/d'));
    }

    /** @test */
    public function 「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        $admin = User::factory()->create(['role' => 1]);

        $nextDay = Carbon::now()->addDay();

        $attendanceNext = Attendance::factory()->create([
            'user_id' => $admin->id,
            'date' => $nextDay->toDateString(),
            'clock_in' => $nextDay->copy()->setTime(9, 0)->toDateTimeString(),
            'clock_out' => $nextDay->copy()->setTime(18, 0)->toDateTimeString(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list?date=' . $nextDay->toDateString());

        $response->assertStatus(200);

        $response->assertSeeText($attendanceNext->date->format('m/d'));
    }
}
