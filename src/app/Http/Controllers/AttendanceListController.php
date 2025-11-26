<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\Attendance;

class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $userId = Auth::id();

        // 現在の月を Carbon インスタンスに
        $current = Carbon::create($year, $month, 1);

        // 月初・月末
        $start = $current->copy()->startOfMonth();
        $end   = $current->copy()->endOfMonth();

        // 月の日付リスト
        $days = collect();
        for ($i = 1; $i <= $current->daysInMonth; $i++) {
            $days->push(Carbon::create($current->year, $current->month, $i));
        }

        // 勤怠データ取得
        $attendances = Attendance::all()->where('user_id', $userId);

        // 前月・翌月を計算
        $prevMonth = $current->copy()->subMonth();
        $nextMonth = $current->copy()->addMonth();

        return view('user.attendance.index', compact(
            'days',
            'attendances',
            'current',
            'prevMonth',
            'nextMonth'
        ));
    }
}
