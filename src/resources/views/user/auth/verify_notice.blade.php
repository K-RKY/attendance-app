@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify_notice.css') }}">
@endsection

@section('content')
<div class="main-container">
    <p class="verify-info">
        登録していただいたメールアドレスに認証メールを送付しました。</br>
        メール認証を完了してください。
    </p>
    <a class="verify-link" href="http://localhost:8025" target="_blank" rel="noopener noreferrer">
        認証はこちらから
    </a>
    <form action="{{ route('verification.send') }}" method="POST">
        @csrf
        <button class="verify-mail-send">認証メールを再送する</button>
    </form>
</div>
@endsection