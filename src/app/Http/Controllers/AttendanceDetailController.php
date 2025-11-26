<?php

namespace App\Http\Controllers;

use App\Models\Attendance;

class AttendanceDetailController extends Controller
{
    public function show($id)
    {
        $attendance = Attendance::with('user', 'breaks')->findOrFail($id);

        $requestStatus = optional($attendance->requests)->status;
        $readonly = ($requestStatus !== 0 && $requestStatus !== 1) ? false : true;


        $remarks = $attendance->remarks;
        $userName = $attendance->user->name;
        $date = $attendance->date;

        return view('user.attendance.show', compact('attendance', 'userName', 'date', 'requestStatus', 'readonly', 'remarks'));
    }
}
