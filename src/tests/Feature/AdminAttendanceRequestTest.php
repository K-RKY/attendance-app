<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Models\AttendanceRequestBreak;
use App\Models\BreakTime;
use Illuminate\Support\Carbon;

class AdminAttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $admin = User::factory()->create(['role' => 1]);

        $attendance = Attendance::create([
            'user_id' => $admin->id,
            'date' => Carbon::today(),
            'clock_in' => Carbon::parse('09:00'),
            'clock_out' => Carbon::parse('18:00'),
            'remarks' => '本日の勤務です',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.detail', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('本日の勤務です');
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.detail', $attendance->id));
        $response->assertStatus(200);

        $response = $this->post(route('admin.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'remarks' => 'テスト'
        ]);

        $response->assertRedirect(route('admin.detail', $attendance->id));

        $response->assertSessionHasErrors('clock_in');
        $this->assertEquals('出勤時間もしくは<br>退勤時間が不適切な値です', session('errors')->first('clock_in'));
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 1]);

        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.detail', $attendance->id));
        $response->assertStatus(200);

        $response = $this->post(route('admin.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['17:30'],
            'break_end' => ['18:00'],
            'remarks' => 'テスト'
        ]);

        $response->assertRedirect(route('admin.detail', $attendance->id));

        $response->assertSessionHasErrors('break_start.0');
        $this->assertEquals('休憩時間が不適切な値です', session('errors')->first('break_start.0'));
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合_エラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.detail', $attendance->id));
        $response->assertStatus(200);

        $response = $this->post(route('admin.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'break_start' => ['12:00'],
            'break_end' => ['17:30'],
            'remarks' => 'テスト'
        ]);

        $response->assertRedirect(route('admin.detail', $attendance->id));

        $response->assertSessionHasErrors('break_end.0');
        $this->assertEquals('休憩時間もしくは<br>退勤時間が不適切な値です', session('errors')->first('break_end.0'));
    }

    /** @test */
    public function 備考欄が未入力の場合のエラーメッセージが表示される()
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.detail', $attendance->id));
        $response->assertStatus(200);

        $response = $this->post(route('admin.request.store', $attendance->id), [
            'date' => '2023-11-26',
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'remarks' => '',
        ]);

        $response->assertRedirect(route('admin.detail', $attendance->id));

        $response->assertSessionHasErrors('remarks');
        $this->assertEquals('備考を記入してください', session('errors')->first('remarks'));
    }

    /** @test */
    public function 承認待ちの修正申請が全て表示されている()
    {
        $user1 = User::factory()->create(['name' => 'ユーザー１',]);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);
        $admin = User::factory()->create(['role' => 1]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
        ]);

        $attendanceRequest1 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'requested_remarks' => $user1->name . '修正データ',
        ]);
        $attendanceRequest2 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'requested_remarks' => $user2->name . '修正データ',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('attendance.request.index'));

        $response->assertStatus(200);
        $response->assertSee($user1->name . '修正データ');
        $response->assertSee($user2->name . '修正データ');
        $response->assertSee('承認待ち');
    }

    /** @test */
    public function 承認済みの修正申請が全て表示されている()
    {
        $user1 = User::factory()->create(['name' => 'ユーザー１',]);
        $user2 = User::factory()->create(['name' => 'ユーザー2']);
        $admin = User::factory()->create(['role' => 1]);

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
        ]);

        $attendanceRequest1 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'requested_remarks' => $user1->name . '修正データ',
            'status' => 1,
        ]);
        $attendanceRequest2 = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'requested_remarks' => $user2->name . '修正データ',
            'status' => 1,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('attendance.request.index') . '?tab=approved');

        $response->assertStatus(200);
        $response->assertSee($user1->name . '修正データ');
        $response->assertSee($user2->name . '修正データ');
        $response->assertSee('承認済み');
    }

    public function 修正申請の詳細内容が正しく表示されている()
    {
        $user = User::factory()->create(['name' => 'ユーザー１',]);
        $admin = User::factory()->create(['role' => 1]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $attendanceRequest = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_remarks' => $user->name . '修正データ',
            'requested_clock_in' => '09:00',
            'requested_clock_out' => '18:00',
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.request.detail', $attendanceRequest->id));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee($user->name . '修正データ');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
    {
        $admin = User::factory()->create(['role' => 1]);
        $user = User::factory()->create();

        $date = now()->toDateString();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $date,
            'clock_in' => $date . ' 09:00:00',
            'clock_out' => $date . ' 18:00:00',
            'remarks' => 'Initial remarks',
            'status' => 0,
        ]);

        $attendanceRequest = AttendanceRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_date' => $date,
            'requested_clock_in' => $date . ' 09:30:00',
            'requested_clock_out' => $date . ' 18:30:00',
            'requested_remarks' => 'Requested remarks',
            'status' => 0,
        ]);

        $this->actingAs($admin);
        $data = [
            'remarks' => 'Approved remarks',
            'clock_in' => '09:30',
            'clock_out' => '18:30',
        ];

        $response = $this->patch(route('admin.request.update', $attendanceRequest->id), $data);

        $response->assertStatus(302);
        $attendanceRequest->refresh();
    }
}
