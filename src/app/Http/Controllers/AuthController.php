<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice');
    }

    public function login(LoginRequest $request)
    {
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'email' => '試行回数が多すぎます。しばらくしてからお試しください',
            ])->withInput($request->only('email'));
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            RateLimiter::clear($throttleKey);

            $user = Auth::user();

            // 管理者チェック
            if ($user->role !== 0) {
                Auth::logout();
                return back()->withErrors([
                    'fail' => '一般ユーザーでログインしてください',
                ]);
            }

            // メール未認証ユーザーならメール認証画面へ
            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('status', '登録されたメールアドレスの認証が必要です。メールを確認してください');
            }

            // 認証済みならHOMEへ
            return redirect()->intended('/');
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'fail' => 'ログイン情報が登録されていません',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
