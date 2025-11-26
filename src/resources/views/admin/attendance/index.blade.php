@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/layouts/table.css') }}">
@endsection

@section('content')
<div class="page-container">
    <div class="attendance-header">
        <p class="page-title">{{ $current->format('Y年m月d日') }}の勤怠</p>
    </div>

    <div class="month-display">
        <nav class="month-nav">
            <a class="month-nav__prev" href="{{ route('admin.index', ['date' => $prevDay->toDateString()]) }}">← 前日</a>
            <p class="month-nav__current">
                🗓️ {{ $current->format('Y/m/d') }}
            </p>
            <a class="month-nav__next" href="{{ route('admin.index', ['date' => $nextDay->toDateString()]) }}">翌日 →
            </a>
        </nav>
    </div>
    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td>{{ $attendance ? $attendance->user->name : '' }}</td>
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
</div>
@endsection