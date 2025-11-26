@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/auth.css') }}">
@endsection

@section('content')
<div class="auth-container">
    <span class="auth-title">ログイン</span>
    <form class="auth-form" action="" method="POST">
        @csrf
        <label class="auth-form__label" for="">メールアドレス</label>
        <input class="auth-form__input" type="email" name="email" value="{{ old('email') }}">
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
            @error('fail')
            <span class="error-message">{{ $message }}</span>
            @enderror
        </div>

        <button class="auth-form__button" type="submit">ログインする</button>
        <a class="auth-form__link" href="{{ route('register') }}">会員登録はこちら</a>
    </form>
</div>
@endsection