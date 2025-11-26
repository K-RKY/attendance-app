@push('css')
<link rel="stylesheet" href="{{ asset('css/layouts/request_index.css') }}">
<link rel="stylesheet" href="{{ asset('css/layouts/table.css') }}">
@endpush


<div class="page-container">
    <div class="attendance-header">
        <p class="page-title">申請一覧</p>
    </div>

    <div class="tab">
        <a class="tab-link {{ request('tab') !== 'approved' ? 'active' : '' }}" href="{{ route('attendance.request.index') }}">承認待ち</a>
        <a class="tab-link {{ request('tab') === 'approved' ? 'active' : '' }}" href="{{ route('attendance.request.index', ['tab' => 'approved']) }}">承認済み</a>
    </div>
    <hr>

    @if ($attendances->isNotEmpty())
    <table>
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $attendance)
            <tr>
                <td>{{ $attendance->status_label }}</td>
                <td>{{ $attendance->attendance->user->name }}</td>
                <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y/m/d') }}</td>
                <td>{{ $attendance->requested_remarks }}</td>
                <td>{{ $attendance->created_at->format('Y/m/d') }}</td>
                <td>
                    @if ($attendance)
                    @if ($user->role == 0)
                    <a class="detail-link" href="{{ route('attendance.request.detail', ['id' => $attendance->id]) }}">詳細</a>
                    @else
                    <a class="detail-link" href="{{ route('admin.request.detail', ['attendance_correct_request_id' => $attendance->id]) }}">詳細</a>
                    @endif
                    @else
                    詳細
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <span>承認待ちの申請はありません</span>
    @endif
</div>