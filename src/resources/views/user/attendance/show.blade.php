@extends('layouts.app')

@section('content')

@if ($requestStatus === 0)
@php
$actionHtml = '<span class="pending-message">*承認待ちのため修正できません。</span>'
@endphp
@elseif ($requestStatus === 1)
@php
$actionHtml = '<button class="submit-button" disabled="true">承認済み</button>'
@endphp
@else
@php
$actionHtml = '<button class="submit-button" type="submit">修正</button>'
@endphp
@endif

@include('layouts.show', [
'formAction' => route('attendance.request.store', ['id' => $attendance->id]),
'actionHtml' => $actionHtml,
])
@endsection