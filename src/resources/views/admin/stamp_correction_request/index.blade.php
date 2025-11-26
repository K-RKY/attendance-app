@extends('layouts.app')

@section('content')
@include('layouts.request_index', [
'dtailLink' => route('admin.request.detail', ['id' => $attendance->id])])
@endsection