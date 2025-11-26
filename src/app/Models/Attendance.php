<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'remarks',
        'status',
    ];

    /* ------------------------------------
        リレーション
    ------------------------------------ */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function requests()
    {
        return $this->hasOne(AttendanceRequest::class);
    }

    protected $casts = [
        'date' => 'datetime',
    ];


    /* ------------------------------------
        アクセサ（表示用）
    ------------------------------------ */

    /** YYYY-MM-DD → m/d(曜日) */
    public function getDateFormattedAttribute()
    {
        $date = $this->date instanceof Carbon ? $this->date : Carbon::parse($this->date);
        return $date->format('m/d') . '(' . ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] . ')';
    }

    private function getWeekdayLabel()
    {
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        return $week[Carbon::parse($this->date)->dayOfWeek];
    }


    /** 出勤 */
    public function getClockInFormattedAttribute()
    {
        return $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i') : '';
    }

    /** 退勤 */
    public function getClockOutFormattedAttribute()
    {
        return $this->clock_out ? Carbon::parse($this->clock_out)->format('H:i') : '';
    }


    /* ------------------------------------
        休憩・労働 合計時間
    ------------------------------------ */

    /** 合計休憩（秒） */
    public function getBreakSecondsAttribute()
    {
        return $this->breaks->sum('duration_seconds');
    }

    /** 合計休憩（H:i） */
    public function getBreakFormattedAttribute()
    {
        $sec = $this->break_seconds;
        return $sec > 0 ? gmdate('H:i', $sec) : '';
    }


    /** 総労働時間（秒） */
    public function getWorkSecondsAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $work = Carbon::parse($this->clock_out)->diffInSeconds(Carbon::parse($this->clock_in));
        return max($work - $this->break_seconds, 0);
    }

    /** 総労働時間（H:i） */
    public function getWorkFormattedAttribute()
    {
        $sec = $this->work_seconds;
        return $sec > 0 ? gmdate('H:i', $sec) : '';
    }


    /* ------------------------------------
        ステータス名
    ------------------------------------ */

    public function getStatusLabelAttribute()
    {
        return [
            0 => '勤務外',
            1 => '出勤中',
            2 => '休憩中',
            3 => '退勤済',
        ][$this->status] ?? '勤務外';
    }
}
