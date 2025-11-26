<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'requested_date',
        'requested_clock_in',
        'requested_clock_out',
        'requested_remarks',
        'status',
    ];

    // 勤怠記録に属する: 多対1
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    // 修正申請の休憩（AttendanceRequestBreak）: 1対多
    public function breaks()
    {
        return $this->hasMany(AttendanceRequestBreak::class);
    }

    protected $casts = [
        'requested_date' => 'date',
        'created_at' => 'datetime'
    ];

    public function getStatusLabelAttribute()
    {
        return [
            0 => '承認待ち',
            1 => '承認済み',
        ][$this->status] ?? '承認待ち';
    }

    /** 出勤 */
    public function getClockInFormattedAttribute()
    {
        return $this->requested_clock_in ? Carbon::parse($this->requested_clock_in)->format('H:i') : '';
    }

    /** 退勤 */
    public function getClockOutFormattedAttribute()
    {
        return $this->requested_clock_out ? Carbon::parse($this->requested_clock_out)->format('H:i') : '';
    }
}
