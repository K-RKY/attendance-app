<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class AdminAuthController extends Controller
{
    public function showLoginFrom()
    {
        return view('admin/login');
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
            if ($user->role !== 1) {
                Auth::logout();
                return back()->withErrors([
                    'fail' => '管理者権限がありません',
                ]);
            }

            if (!$user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice')
                    ->with('status', '管理者アカウントのメール認証が必要です。メールを確認してください');
            }

            return redirect()->route('admin.index');
        }

        // 認証失敗
        RateLimiter::hit($throttleKey);
        return back()->withErrors([
            'fail' => 'ログイン情報が登録されていません',
        ]);
    }
}
