<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\AttendanceEditRequestController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\User\AttendanceViewController;
use App\Http\Controllers\BreakController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// 認証ルート
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
});

// メール認証案内ページ
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

//メール認証リンクの処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill(); // 認証済みにする
    return redirect('/attendance'); // 認証後のリダイレクト先（お好みで変更OK）
})->middleware(['auth', 'signed'])->name('verification.verify');

// 認証メールの再送信
Route::post('/email/verification-notification', function (Illuminate\Http\Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('message', '認証リンクを再送信しました！');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// Fortify のルーティング（ログイン・登録）は Fortify が自動で定義

// 認証後のルート（一般ユーザー）
Route::middleware(['auth', 'verified'])->group(function () {
    // 出勤登録
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    //休憩登録
    Route::post('/break/start', [BreakController::class, 'start'])->name('break.start');
    Route::post('/break/end', [BreakController::class, 'end'])->name('break.end');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceViewController::class, 'show'])->name('attendance.show');

    // 修正申請 保存処理（POST）
    Route::post('/stamp_correction_request', [AttendanceEditRequestController::class, 'store'])->name('user.request.store');

    // 修正申請 一覧表示（GET）
    Route::get('/stamp_correction_request/list', [AttendanceEditRequestController::class, 'index'])->name('user.request.index');
});
