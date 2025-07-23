<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\UserController as AdminUserController;
use App\Http\Controllers\Admin\AttendanceEditRequestController as AdminAttendanceEditRequestController;

// 管理者ルーティング
Route::prefix('admin')->name('admin.')->group(function () {
    // 管理者ログイン
    Route::get('login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminLoginController::class, 'login']);
    Route::post('logout', [AdminLoginController::class, 'logout'])->name('logout');

    // 認証後の管理者画面
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('dashboard', fn() => redirect()->route('admin.attendances.index'))->name('dashboard');

        Route::get('attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
        Route::get('attendances/{id}', [AdminAttendanceController::class, 'show'])->name('attendances.show');

        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/attendances', [AdminUserController::class, 'userAttendances'])->name('users.attendances');

        Route::get('requests', [AdminAttendanceEditRequestController::class, 'index'])->name('requests.index');
        Route::get('requests/{id}', [AdminAttendanceEditRequestController::class, 'show'])->name('requests.show');
        Route::post('requests/{id}', [AdminAttendanceEditRequestController::class, 'approve'])->name('requests.approve');
    });
});
