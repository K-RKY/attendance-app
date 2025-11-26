@extends('layouts.app')

@section('content')

@if ($requestStatus === 0)
@php
$actionHtml = '<button class="submit-button" type="submit">承認</button>'
@endphp
@elseif ($requestStatus === 1)
@php
$actionHtml = '<button class="submit-button" disabled="true">承認済み</button>'
@endphp
@else
@endif

@include('layouts.show', [
'formAction' => route('admin.request.update', ['attendance_correct_request_id' => $attendance->id]),
'method' => 'PATCH',
'actionHtml' => $actionHtml,
])
@endsection