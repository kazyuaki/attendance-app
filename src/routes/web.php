<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
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
    return view('welcome');
});

// 管理者ルート
Route::prefix('admin')->name('admin.')->group(function () {
    // 管理者ログイン
    Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminLoginController::class, 'login']);
    Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');

    // ログイン後（認証が必要）
    Route::middleware('auth:admin')->group(function () {
        Route::get('dashboard', fn() => redirect()->route('admin.attendances.index'))->name('dashboard');
        Route::get('attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
    });
});
