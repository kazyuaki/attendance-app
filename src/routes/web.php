<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\User\AttendanceController;
use App\Http\Controllers\User\AttendanceEditRequestController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\User\AttendanceViewController;
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
// Fortify のルーティング（ログイン・登録）は Fortify が自動で定義

// 認証後のルート（一般ユーザー）
Route::middleware(['auth', 'verified'])->group(function () {
    // 出勤登録
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceViewController::class, 'show'])->name('attendance.show');

    // 修正申請 保存処理（POST）
    Route::post('/stamp_correction_request', [AttendanceEditRequestController::class, 'store'])->name('user.request.store');

    // 修正申請 一覧表示（GET）
    Route::get('/stamp_correction_request/list', [AttendanceEditRequestController::class, 'index'])->name('user.request.index');
});
