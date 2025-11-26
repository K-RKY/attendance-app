<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAttendanceRequest;
use Illuminate\Http\Request;
use App\Models\AttendanceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\AttendanceRequestBreak;

class AttendanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab');
        $status = $tab !== 'approved' ? 0 : 1;

        $query = AttendanceRequest::with('attendance.user', 'attendance.breaks')
            ->where('status', $status);

        $user = auth()->user();

        if ($user->role === 0) {
            // 一般ユーザー: 自分の勤怠だけ
            $query->whereHas('attendance', function ($q) {
                $q->where('user_id', Auth::id());
            });
        }

        $attendances = $query->get();

        return view('user.stamp_correction_request.index', compact('attendances', 'user'));
    }


    public function store(UpdateAttendanceRequest $request, $id)
    {
        $attendanceDate = Carbon::parse($request->date)->toDateString();

        $requested_clock_in = $request->clock_in
            ? $attendanceDate . ' ' . $request->clock_in . ':00'
            : null;

        $requested_clock_out = $request->clock_out
            ? $attendanceDate . ' ' . $request->clock_out . ':00'
            : null;

        // 修正申請
        $attendanceRequest = AttendanceRequest::create([
            'attendance_id' => $id,
            'requested_date' => $request->date,
            'requested_clock_in' => $requested_clock_in,
            'requested_clock_out' => $requested_clock_out,
            'requested_remarks' => $request->remarks,
            'status' => 0,
        ]);


        // 休憩申請
        $breakStarts = $request->break_start ?? [];
        $breakEnds   = $request->break_end ?? [];

        foreach ($breakStarts as $i => $start) {

            if (!$start && empty($breakEnds[$i])) {
                continue;
            }

            $startDateTime = $start
                ? $attendanceDate . ' ' . $start . ':00'
                : null;

            $endDateTime = !empty($breakEnds[$i])
                ? $attendanceDate . ' ' . $breakEnds[$i] . ':00'
                : null;

            AttendanceRequestBreak::create([
                'attendance_request_id' => $attendanceRequest->id,
                'break_start'           => $startDateTime,
                'break_end'             => $endDateTime,
            ]);
        }

        return redirect()
            ->route('attendance.request.index')
            ->with('status', '修正申請しました。');
    }


    public function show($id)
    {
        $attendance = AttendanceRequest::with('attendance.user', 'breaks')->findOrFail($id);

        $requestStatus = optional($attendance)->status;
        $requestStatus !== 0 ? $readonly = false : $readonly = true;

        $remarks = $attendance->requested_remarks;
        $userName = $attendance->attendance->user->name;
        $date = $attendance->requested_date;

        return view('user.attendance.show', compact('attendance', 'userName', 'date', 'requestStatus', 'readonly', 'remarks'));
    }
}
