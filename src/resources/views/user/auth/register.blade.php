@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <span class="auth-title">会員登録</span>
    <form class="auth-form" action="{{ route('register') }}" method="POST">
        @csrf
        <label class="auth-form__label" for="">名前</label>
        <input class="auth-form__input" type="text" name="name" value="{{ old('name') }}">
        <div class="error-container">
            @error('name')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <label class="auth-form__label" for="">メールアドレス</label>
        <input class="auth-form__input" type="email" name="email" value="{{ old('email')}}">
        <div class="error-container">
            @error('email')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <label class="auth-form__label" for="">パスワード</label>
        <input class="auth-form__input" type="password" name="password">
        <div class="error-container">
            @error('password')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <label class="auth-form__label" for="">パスワード確認</label>
        <input class="auth-form__input" type="password" name="password_confirmation">

        <button class="auth-form__button" type="submit">登録する</button>
        <a class="auth-form__link" href="{{ route('login') }}">ログインはこちら</a>
    </form>
</div>
@endsection