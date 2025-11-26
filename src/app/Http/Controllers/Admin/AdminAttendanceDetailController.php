<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;

class AdminAttendanceDetailController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breaks')->findOrFail($id);

        $requestStatus = optional($attendance->requests)->status;
        $readonly = ($requestStatus !== 0 && $requestStatus !== 1) ? false : true;

        $remarks = $attendance->remarks;
        $userName = $attendance->user->name;
        $date = $attendance->date;

        return view('admin.attendance.show', compact('attendance', 'userName', 'date', 'requestStatus', 'readonly', 'remarks'));
    }
}
