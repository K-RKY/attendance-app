<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Attendance;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->query('date', now()->toDateString());

        $current = Carbon::parse($date);

        $day = $current->copy();

        $attendances = Attendance::with('user')
            ->where('date', $day)
            ->whereHas('user', function ($query) {
                $query->where('role', 0);
            })
            ->get();

        // 前月・翌月を計算
        $prevDay = $current->copy()->subDay();
        $nextDay = $current->copy()->addDay();

        return view('admin.attendance.index', compact(
            'day',
            'attendances',
            'current',
            'prevDay',
            'nextDay'
        ));
    }
}
