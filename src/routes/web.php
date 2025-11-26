<?php

use App\Http\Controllers\AttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminAttendanceDetailController;
use App\Http\Controllers\Admin\AdminAttendanceRequestController;
use App\Http\Controllers\Admin\StaffListController;

Route::middleware(['auth', 'verified', 'check.role:0'])->group(function () {
    // 勤怠打刻画面
    Route::get('/', [AttendanceController::class, 'attendancePage']);

    // 出勤
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');

    // 休憩開始
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.breakStart');

    // 休憩終了
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.breakEnd');

    // 退勤
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->name('attendance.clockOut');

    // 勤怠一覧画面
    Route::get('/attendance/list', [AttendanceListController::class, 'index'])
        ->name('attendance.list');

    // 勤怠詳細画面
    Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show'])->name('attendance.detail');

    // 承認待ち勤怠詳細画面
    Route::get('attendance/request/detail/{id}', [AttendanceRequestController::class, 'show'])->name('attendance.request.detail');

    // 勤怠修正申請
    Route::post('/attendance/detail/{id}', [AttendanceRequestController::class, 'store'])->name('attendance.request.store');
});

// 申請一覧画面
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])
        ->name('attendance.request.index');
});

// ログイン画面
Route::get('/login', function () {
    return view('user.auth.login');
});
// 会員登録
Route::post('/register', [AuthController::class, 'register'])->name('register');
// ログイン
Route::post('/login', [AuthController::class, 'login'])->name('login');
// ログアウト
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// メール認証誘導画面
Route::get('/verify/notice', function () {
    return view('user.auth.verify_notice');
})->middleware('auth')->name('verification.notice');

// メール認証リンククリック時
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect('/');
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

// 認証メール再送信
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('status', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 管理者画面
Route::prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginFrom']);
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login');
    });

// 管理者権限
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'check.role:1'])
    ->group(function () {
        // 勤怠一覧
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('index');
        // 勤怠詳細
        Route::get('/attendance/{id}', [AdminAttendanceDetailController::class, 'show'])->name('detail');
        // 勤怠修正
        Route::post('/attendance/{id}', [AdminAttendanceRequestController::class, 'store'])->name('request.store');
        // 申請詳細
        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceRequestController::class, 'show'])->name('request.detail');
        // 申請承認
        Route::patch('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceRequestController::class, 'update'])->name('request.update');
        //スタッフ一覧
        Route::get('/staff/list', [StaffListController::class, 'index'])->name('staff.index');
        // スタッフ別勤怠一覧
        Route::get('/attendance/staff/{id}', [StaffListController::class, 'staffAttendance'])->name('staff.attendance.list');
        // CSV出力
        Route::get('attendance/staff/{id}/csv', [StaffListController::class, 'staffAttendanceCsv'])
            ->name('attendance.csv');
    });
