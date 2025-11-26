<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\AttendanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Attendance;


class AdminAttendanceRequestController extends Controller
{
    public function store(UpdateAttendanceRequest $request, $id)
    {
        $requested = $this->parseRequestedTimes($request);
        $attendance = Attendance::with('breaks')->where('id', $id);

        $attendance->update(
            [
                'clock_in' => $requested['clock_in'],
                'clock_out' => $requested['clock_out'],
                'remarks' => $request->remarks,
            ]
        );

        $submittedBreaks = $request->breaks ?? []; // 管理者画面の入力データ。配列で複数件

        foreach ($submittedBreaks as $breakInput) {
            if (!empty($breakInput['id'])) {
                // 既存休憩の更新
                $break = $attendance->breaks()->find($breakInput['id']);
                if ($break) {
                    $break->update([
                        'break_start' => $breakInput['break_start'],
                        'break_end'   => $breakInput['break_end'],
                    ]);
                }
            } else {
                // 新規休憩の作成
                $attendance->breaks()->create([
                    'break_start' => $breakInput['break_start'],
                    'break_end'   => $breakInput['break_end'],
                ]);
            }
        }

        return redirect()->route('admin.request.index')->with('status', '修正しました。');
    }

    public function update(UpdateAttendanceRequest $request, $id)
    {
        $requested = $this->parseRequestedTimes($request);

        $attendanceRequest = AttendanceRequest::with(['attendance', 'breaks'])->findOrFail($id);

        $attendanceRequest->update([
            'status' => 1,
        ]);

        $attendance = $attendanceRequest->attendance;
        if ($attendance) {
            $attendance->update([
                'date' => $requested['date'],
                'clock_in' => $requested['clock_in'],
                'clock_out' => $requested['clock_out'],
                'remarks' => $request->remarks,
            ]);

            // attendance_request_breaks に休憩データがある場合のみ反映
            if ($attendanceRequest->breaks->isNotEmpty()) {

                // 既存の attendance_breaks を削除
                $attendance->breaks()->delete();

                // 新しい休憩データを作成
                foreach ($attendanceRequest->breaks as $break) {
                    $attendance->breaks()->create([
                        'break_start' => $break->break_start,
                        'break_end'   => $break->break_end,
                    ]);
                }
            }
            // attendance_request_breaks が空なら何もしない
        }

        return back()->with('status', '承認しました。');
    }



    public function show($id)
    {
        $attendance = AttendanceRequest::with('attendance.user', 'attendance.breaks')->findOrFail($id);

        $requestStatus = optional($attendance)->status;
        $requestStatus !== 0 ? $readonly = false : $readonly = true;

        $remarks = $attendance->requested_remarks;
        $userName = $attendance->attendance->user->name;
        $date = $attendance->requested_date;

        return view('admin.stamp_correction_request.show', compact('attendance', 'userName', 'date', 'requestStatus', 'readonly', 'remarks'));
    }

    /**
     * リクエストから日付と出退勤時刻を Carbon で整形
     */
    private function parseRequestedTimes(Request $request)
    {
        $attendanceDate = Carbon::parse($request->date)->toDateString();

        $clock_in = $request->clock_in
            ? $attendanceDate . ' ' . $request->clock_in . ':00'
            : null;

        $clock_out = $request->clock_out
            ? $attendanceDate . ' ' . $request->clock_out . ':00'
            : null;

        return [
            'date' => $attendanceDate,
            'clock_in' => $clock_in,
            'clock_out' => $clock_out,
        ];
    }
}
