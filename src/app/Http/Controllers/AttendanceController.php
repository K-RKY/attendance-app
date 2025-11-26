<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function attendancePage()
    {
        $attendance = Attendance::firstOrNew([
            'user_id' => Auth::id(),
            'date'    => now()->toDateString()
        ]);

        return view('user.attendance.create', compact('attendance'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 既に出勤済みなら何もしない
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            return back()->withErrors(['error' => '出勤済み']);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => Carbon::now(),
            'status' => 1, // 出勤中
        ]);

        return view('user.attendance.create', compact('attendance'));
    }

    /**
     * 休憩入
     */
    public function breakStart()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->firstOrFail();

        // 状態を休憩中へ
        $attendance->update([
            'status' => 2,
        ]);

        // breaks テーブルに追加
        $attendance->breaks()->create([
            'break_start' => Carbon::now(),
        ]);

        return view('user.attendance.create', compact('attendance'));
    }

    /**
     * 休憩戻
     */
    public function breakEnd()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->firstOrFail();

        // 最新の break レコードを取得
        $break = $attendance->breaks()->latest()->first();

        if ($break && !$break->break_end) {
            $break->update([
                'break_end' => Carbon::now(),
            ]);
        }

        // 出勤中に戻す
        $attendance->update([
            'status' => 1,
        ]);

        return view('user.attendance.create', compact('attendance'));
    }

    /**
     * 退勤
     */
    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->firstOrFail();

        // 退勤記録
        $attendance->update([
            'clock_out' => Carbon::now(),
            'status' => 3, // 退勤済
        ]);

        return view('user.attendance.create', compact('attendance'));
    }
}
