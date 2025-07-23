<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AttendanceController;
use App\Http\Controllers\AttendanceEditRequestController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Auth\RegisteredAdminUserController;
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

Route::get('/register', [RegisteredUserController::class, 'create'])
    ->middleware('guest')
    ->name('register');
Route::post('/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register.store');
// Fortify のルーティング（ログイン・登録）は Fortify が自動で定義

// 認証後のルート（一般ユーザー）
Route::middleware(['auth', 'verified'])->group(function () {
    // 出勤登録
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠詳細
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show'])->name('attendance.show');

    // 申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceEditRequestController::class, 'index'])->name('request.index');
});
