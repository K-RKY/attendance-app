@extends('layouts.app')

@section('content')
<div class="page-container">
    <div class="attendance-header">
        <p class="page-title">勤怠一覧</p>
    </div>

    <!-- tableのコンポーネント呼び出し -->
    @include('layouts.table')
</div>
@endsection