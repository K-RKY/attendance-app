<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'remarks' => 'テスト'
        ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));

        $response->assertSessionHasErrors('clock_in');
        $this->assertEquals('出勤時間もしくは<br>退勤時間が不適切な値です', session('errors')->first('clock_in'));
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['17:30'],
            'break_end' => ['18:00'],
            'remarks' => 'テスト'
        ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));

        $response->assertSessionHasErrors('break_start.0');
        $this->assertEquals('休憩時間が不適切な値です', session('errors')->first('break_start.0'));
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['17:30'],
            'remarks' => 'テスト'
        ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));

        $response->assertSessionHasErrors('break_end.0');
        $this->assertEquals('休憩時間もしくは<br>退勤時間が不適切な値です', session('errors')->first('break_end.0'));
    }

    /** @test */
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'remarks' => '',
        ]);

        $response->assertRedirect(route('attendance.detail', $attendance->id));

        $response->assertSessionHasErrors('remarks');
        $this->assertEquals('備考を記入してください', session('errors')->first('remarks'));
    }

    /** @test */
    public function 修正申請処理が実行される()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.detail', $attendance->id));
        $response->assertStatus(200);

        $response = $this->post(route('attendance.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '08:30',
            'clock_out' => '17:30',
            'remarks' => '修正申請テスト'
        ]);

        $this->assertDatabaseHas('attendance_requests', [
            'attendance_id' => $attendance->id,
            'status' => 0,
            'requested_remarks' => '修正申請テスト',
        ]);
    }

    /** @test */
    public function 「承認待ち」にログインユーザーが行った申請が全て表示されていること()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('attendance.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '08:30',
            'clock_out' => '17:30',
            'remarks' => '申請テスト'
        ]);

        $response = $this->get(route('attendance.request.index'));
        $response->assertStatus(200);

        $response->assertSee('申請テスト');
    }

    /** @test */
    public function 「承認済み」に管理者が承認した修正申請が全て表示されている()
    {
        $user = User::factory()->create(['role' => 0]);
        $this->actingAs($user);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $attendanceRequest = \App\Models\AttendanceRequest::create([
            'attendance_id' => $attendance->id,
            'requested_date' => '2025-11-26',
            'requested_clock_in' => '2025-11-26 08:30:00',
            'requested_clock_out' => '2025-11-26 17:30:00',
            'requested_remarks' => '申請テスト',
            'status' => 1,
        ]);

        $response = $this->get(route('attendance.request.index') . '?tab=approved');

        $response->assertStatus(200);

        $response->assertSee('申請テスト');
    }

    /** @test */
    public function 各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('attendance.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '08:30',
            'clock_out' => '17:30',
            'remarks' => '申請テスト'
        ]);

        $response = $this->get(route('attendance.request.index'));
        $response->assertStatus(200);

        $attendanceRequest = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)->first();
        $response = $this->get(route('attendance.request.detail', $attendanceRequest->id));
        $response->assertStatus(200);
    }
}
