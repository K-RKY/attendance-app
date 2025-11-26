@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="attendance-header">
        <p class="page-title">{{ $user->name }}の勤怠</p>
    </div>

    @push('css')
    <link rel="stylesheet" href="{{ asset('css/layouts/table.css') }}">
    @endpush

    <div class="month-display">
        <nav class="month-nav">
            <a class="month-nav__prev" href="{{ route('admin.staff.attendance.list', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'id' => $user->id]) }}">← 前月</a>
            <p class="month-nav__current">🗓️ {{ $current->year . '/' . $current->month }}</p>
            <a class="month-nav__next" href="{{ route('admin.staff.attendance.list', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'id' => $user->id]) }}">翌月 →</a>
        </nav>
    </div>
    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($days as $day)
            @php
            $attendance = $attendances->first(function($a) use ($day) {
            return \Carbon\Carbon::parse($a->date)->toDateString() === $day->toDateString();
            });

            @endphp

            <tr>
                <td>{{ $day->format('m/d') . '(' . ['日','月','火','水','木','金','土'][$day->dayOfWeek] . ')' }}</td>
                <td>{{ $attendance ? $attendance->clock_in_formatted : '' }}</td>
                <td>{{ $attendance ? $attendance->clock_out_formatted : '' }}</td>
                <td>{{ $attendance ? $attendance->break_formatted : '' }}</td>
                <td>{{ $attendance ? $attendance->work_formatted :'' }}</td>
                <td>
                    @if ($attendance)
                    <a class="detail-link" href="{{ route('admin.detail', ['id' => $attendance->id]) }}">詳細</a>
                    @else
                    詳細
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="submit-button-wrap">
        <a class="submit-button" href="{{ route('admin.attendance.csv', ['id' => $user->id, 'year' => $current->year, 'month' => $current->month]) }}">CSV出力</a>
    </div>
</div>
@endsection