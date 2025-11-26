@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/layouts/table.css') }}">
@endsection

@section('content')
<div class="page-container">
    <div class="attendance-header">
        <p class="page-title">スタッフ一覧</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
            <tr>
                <td>{{ $user ? $user->name : '' }}</td>
                <td>{{ $user ? $user->email : '' }}</td>
                <td>
                    @if ($user)
                    <a class="detail-link" href="{{ route('admin.staff.attendance.list', ['id' => $user->id]) }}">詳細</a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection