<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class StaffListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $admin = User::factory()->create(['role' => 1]);

        $user1 = User::factory()->create(['role' => 0, 'name' => 'User One', 'email' => 'userone@example.com']);
        $user2 = User::factory()->create(['role' => 0, 'name' => 'User Two', 'email' => 'usertwo@example.com']);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff.index'));

        $response->assertSee('User One');
        $response->assertSee('userone@example.com');
        $response->assertSee('User Two');
        $response->assertSee('usertwo@example.com');
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $admin = User::factory()->create(['role' => 1]);

        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->toDateString().''.' 08:00:00',
            'clock_out' => now()->toDateString().' 17:00:00',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff.attendance.list', ['id' => $user->id]));

        $response->assertSee($attendance->date->format('m/d'));
        $response->assertSee('08:00');
        $response->assertSee('17:00');
    }

    /** @test */
    public function 「前日」を押下した時に表示月の前日の情報が表示される()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 1]);

        $prevDay = Carbon::now()->subDay();
        $attendancePrev = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $prevDay->copy(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=' . $prevDay->toDateString());

        $response->assertStatus(200);

        $expectedDateText = $attendancePrev->date->format('Y/m/d');

        $response->assertSeeText($expectedDateText);
    }

    /** @test */
    public function 「翌日」を押下した時に表示月の翌日の情報が表示される()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 1]);

        $nextDay = Carbon::now()->addDay();
        $attendanceNext = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextDay->copy(),
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/attendance/list?date=' . $nextDay->toDateString());

        $response->assertStatus(200);

        $expectedDateText = $attendanceNext->date->format('Y/m/d');

        $response->assertSeeText($expectedDateText);
    }


    /** @test */
    public function 「詳細」を押下すると_その日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 1]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-25',
        ]);

        $detailUrl = '/admin/attendance/' . $attendance->id;

        $response = $this->actingAs($admin)->get($detailUrl);

        $response->assertStatus(200);
        $response->assertViewIs('admin.attendance.show');
        $response->assertViewHas('attendance', function ($viewAttendance) use ($attendance) {
            return $viewAttendance->id === $attendance->id;
        });
    }
}
