@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/user/attendance/create.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <span class="attendance-status">{{ $attendance->status_label }}</span>
    <p class="date-text">{{ now()->isoFormat('YYYY年M月D日(dd)') }}</p>
    <time class="current-time" id="clock">{{ now()->format('H:i') }}</time>
    <div class="button-container">
        @if (($attendance->status ?? 0) === 0)
        <form action="{{ route('attendance.clockIn') }}" method="POST">
            @csrf
            <button class="attendance-black-button">出勤</button>
        </form>
        @elseif ($attendance->status === 1)
        <form action="{{ route('attendance.clockOut') }}" method="POST">
            @csrf
            <button class="attendance-black-button">退勤</button>
        </form>
        <form action="{{ route('attendance.breakStart') }}" method="POST">
            @csrf
            <button class="attendance-white-button">休憩入</button>
        </form>
        @elseif ($attendance->status ===2)
        <form action="{{ route('attendance.breakEnd') }}" method="POST">
            @csrf
            <button class="attendance-white-button">休憩戻</button>
        </form>
        @elseif($attendance->status ===3)
        <p class="attendance-text">お疲れ様でした。</p>
        @endif
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();

        // 時刻を "HH:MM" 形式で整形
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');

        document.getElementById('clock').textContent = `${h}:${m}`;
    }

    updateClock();

    // 1秒ごとに更新
    setInterval(updateClock, 1000);
</script>
@endsection