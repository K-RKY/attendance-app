<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Facades\Response;

class StaffListController extends Controller
{
    public function index()
    {
        $users = User::where('role', 0)->get();

        return view('admin.staff.index', compact('users'));
    }

    public function staffAttendance(Request $request, $id)
    {
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $userId = $id;

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
        $user = User::findOrFail($userId);

        // 前月・翌月を計算
        $prevMonth = $current->copy()->subMonth();
        $nextMonth = $current->copy()->addMonth();

        return view('admin.attendance.staff.index', compact(
            'days',
            'attendances',
            'current',
            'prevMonth',
            'nextMonth',
            'user',
        ));
    }

    public function staffAttendanceCsv(Request $request, $id)
    {
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
        $userId = $id;

        $current = Carbon::create($year, $month, 1);

        $days = collect();
        for ($i = 0; $i < $current->daysInMonth; $i++) {
            $days->push($current->copy()->addDays($i));
        }

        $attendances = Attendance::with('breaks')->where('user_id', $userId)->get();
        $user = User::findOrFail($userId);

        $filename = "{$user->name}_勤怠一覧_{$current->format('Y_m')}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($days, $attendances) {
            $file = fopen('php://output', 'w');

            // CSVヘッダー
            fputcsv($file, ['日付', '出勤', '退勤', '休憩', '合計']);

            $week = ['日', '月', '火', '水', '木', '金', '土'];

            foreach ($days as $day) {
                $attendance = $attendances->first(function ($a) use ($day) {
                    return \Carbon\Carbon::parse($a->date)->toDateString() === $day->toDateString();
                });

                $breakFormatted = '';
                if ($attendance) {
                    // 休憩の合計秒数
                    $breakSeconds = $attendance->breaks->sum('duration_seconds');
                    $breakFormatted = $breakSeconds > 0 ? gmdate('H:i', $breakSeconds) : '';
                }

                fputcsv($file, [
                    $attendance
                        ? $day->format('m/d') . '(' . $week[$day->dayOfWeek] . ')'
                        : $day->format('m/d') . '(' . $week[$day->dayOfWeek] . ')',
                    $attendance ? $attendance->clock_in_formatted : '',
                    $attendance ? $attendance->clock_out_formatted : '',
                    $breakFormatted,
                    $attendance ? $attendance->work_formatted : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
