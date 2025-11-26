<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AttendanceRequestBreak extends Model
{
    use HasFactory;

    protected $table = 'attendance_request_breaks';

    protected $fillable = [
        'attendance_request_id',
        'break_start',
        'break_end',
    ];

    public function attendanceRequest()
    {
        return $this->belongsTo(AttendanceRequest::class);
    }

    /* ---------------------------
         表示用アクセサ
    ---------------------------- */

    // 開始時刻
    public function getBreakStartFormattedAttribute()
    {
        return $this->break_start
            ? Carbon::parse($this->break_start)->format('H:i')
            : '';
    }

    // 終了時刻
    public function getBreakEndFormattedAttribute()
    {
        return $this->break_end
            ? Carbon::parse($this->break_end)->format('H:i')
            : '';
    }

    // 休憩時間（秒）
    public function getDurationSecondsAttribute()
    {
        if (!$this->break_start || !$this->break_end) {
            return 0;
        }

        return Carbon::parse($this->break_end)
            ->diffInSeconds(Carbon::parse($this->break_start));
    }

    // 休憩時間（HH:MM）
    public function getDurationFormattedAttribute()
    {
        $seconds = $this->duration_seconds;
        return $seconds > 0 ? gmdate('H:i', $seconds) : '';
    }
}
