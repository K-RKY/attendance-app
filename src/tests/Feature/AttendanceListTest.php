<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create();

        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-01',
            'clock_in' => '2025-11-01 09:00:00',
            'clock_out' => '2025-11-01 18:00:00',
        ]);

        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-02',
            'clock_in' => '2025-11-02 09:00:00',
            'clock_out' => '2025-11-02 18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSeeText('11/01(土)');
        $response->assertSeeText('11/02(日)');
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance/list');

        $currentMonth = Carbon::now()->format('Y-m');

        $response->assertStatus(200);
        $response->assertViewHas('current', function ($current) use ($currentMonth) {
            return $current->format('Y-m') === $currentMonth;
        });
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create();

        $prevMonth = Carbon::now()->subMonth();
        $attendancePrev = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $prevMonth->copy()->day(1),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?year=' . $prevMonth->year . '&month=' . $prevMonth->month);

        $response->assertStatus(200);

        $weekday = $this->getJapaneseWeekday($attendancePrev->date);
        $expectedDateText = $attendancePrev->date->format('m/d') . "({$weekday})";

        $response->assertSeeText($expectedDateText);
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $user = User::factory()->create();

        $nextMonth = Carbon::now()->addMonth();
        $attendanceNext = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => $nextMonth->copy()->day(1),
        ]);

        $response = $this->actingAs($user)
            ->get('/attendance/list?year=' . $nextMonth->year . '&month=' . $nextMonth->month);

        $response->assertStatus(200);

        $weekday = $this->getJapaneseWeekday($attendanceNext->date);
        $expectedDateText = $attendanceNext->date->format('m/d') . "({$weekday})";

        $response->assertSeeText($expectedDateText);
    }


    /** @test */
    public function 「詳細」を押下すると_その日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => '2025-11-25',
        ]);

        $detailUrl = '/attendance/detail/' . $attendance->id;

        $response = $this->actingAs($user)->get($detailUrl);

        $response->assertStatus(200);
        $response->assertViewIs('user.attendance.show');
        $response->assertViewHas('attendance', function ($viewAttendance) use ($attendance) {
            return $viewAttendance->id === $attendance->id;
        });
    }

    protected function getJapaneseWeekday(\Carbon\Carbon $date)
    {
        $weekdays = [
            'Sun' => '日',
            'Mon' => '月',
            'Tue' => '火',
            'Wed' => '水',
            'Thu' => '木',
            'Fri' => '金',
            'Sat' => '土',
        ];

        return $weekdays[$date->format('D')];
    }
}
