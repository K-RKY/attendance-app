<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 現在の日時情報がUIと同じ形式で出力されている()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $now = Carbon::now();
        $expectedDate = $now->format('Y年m月d日') . '(' . ['日', '月', '火', '水', '木', '金', '土'][$now->dayOfWeek] . ')';

        $response->assertSee($expectedDate);
    }

    /** @test */
    public function 勤務外の場合_勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 0, // 勤務外
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertSee('勤務外');
    }

    /** @test */
    public function 出勤中の場合_勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 1, // 出勤中
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合_勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 2, // 休憩中
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合_勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'status' => 3, // 退勤済
        ]);

        $response = $this->actingAs($user)->get('/');
        $response->assertSee('退勤済');
    }

    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('出勤');

        $response = $this->post(route('attendance.clockIn'));

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($attendance);
        $this->assertEquals(1, $attendance->status);

        $response->assertSee('出勤中');
    }


    /** @test */
    public function 出勤は一日一回だけ()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now()->subHours(1),
            'status' => 3,
        ]);

        $this->actingAs($user);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('出勤');

        $response = $this->post(route('attendance.clockIn'));

        $response->assertSessionHasErrors('error');
    }


    /** @test */
    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.clockIn'));

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', Carbon::today()->toDateString())
            ->first();

        $this->assertNotNull($attendance);

        $clockInFormatted = Carbon::parse($attendance->clock_in)->format('H:i');

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);

        $response->assertSee($clockInFormatted);
    }

    /** @test */
    public function 休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(2),
            'status' => 1,
        ]);

        $this->actingAs($user);

        $response = $this->get('/');
        $response->assertSee('休憩入');

        $response = $this->post(route('attendance.breakStart'));

        $attendance->refresh();
        $response->assertSee('休憩中');

        $this->assertEquals(2, $attendance->status);
        $this->assertDatabaseHas('breaks', [
            'attendance_id' => $attendance->id,
        ]);
    }


    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(3),
            'status' => 1,
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.breakStart'));
        $attendance->refresh();

        $this->post(route('attendance.breakEnd'));
        $attendance->refresh();

        $response = $this->post(route('attendance.breakStart'));

        $response->assertSee('休憩中');
        $this->assertEquals(2, $attendance->refresh()->status);

        $response = $this->get('/');
        $response->assertSee('休憩戻');
    }


    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(3),
            'status' => 1,
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.breakStart'));
        $attendance->refresh();

        $break = $attendance->breaks()->latest()->first();
        $this->assertNotNull($break);

        $response = $this->post(route('attendance.breakEnd'));

        $attendance->refresh();

        $response->assertSee('出勤中');

        $this->assertEquals(1, $attendance->status);
        $this->assertNotNull($break->refresh()->break_end);
    }


    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(4),
            'status' => 1,
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.breakStart'));
        $this->post(route('attendance.breakEnd'));
        $this->post(route('attendance.breakStart'));

        $response = $this->get('/');
        $response->assertSee('休憩戻');
    }


    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(4),
            'status' => 1,
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.breakStart'));
        $break = $attendance->breaks()->latest()->first();

        $break->update([
            'break_end' => Carbon::parse($break->break_start)->addMinutes(30)
        ]);

        $attendance->refresh();

        $breakTotal = $attendance->break_formatted;

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);

        $response->assertSee($breakTotal);
    }

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        // 出勤中ユーザー
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        // 出勤中の勤怠
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => today()->toDateString(),
            'clock_in' => now()->subHours(4),
            'status' => 1, // 出勤中
        ]);

        $this->actingAs($user);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('退勤');

        $response = $this->post(route('attendance.clockOut'));
        $attendance->refresh();

        $this->assertEquals(3, $attendance->status);

        $response->assertSee('退勤済');
    }


    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        // 勤務外ユーザー
        $user = User::factory()->create([
            'role' => 0,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.clockIn'));

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', today()->toDateString())
            ->first();

        $attendance->update([
            'clock_out' => Carbon::parse($attendance->clock_in)->addHours(8),
            'status' => 3,
        ]);

        $clockOutFormatted = Carbon::parse($attendance->clock_out)->format('H:i');

        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);

        $response->assertSee($clockOutFormatted);
    }
}
