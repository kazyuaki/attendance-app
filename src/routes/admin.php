<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AttendanceEditRequestController as AdminAttendanceEditRequestController;

Route::get('/test', function () {
    return 'admin route is working!';
});

// 管理者ルーティング
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // 認証後の管理者画面
    Route::middleware(['auth:admin'])->group(function () {
        Route::get('dashboard', fn() => redirect()->route('attendances.index'))->name('dashboard');

        Route::get('attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
        Route::get('attendances/{id}', [AdminAttendanceController::class, 'show'])->name('attendances.show');
        Route::patch('attendances/{id}', [AdminAttendanceController::class, 'update'])->name('attendances.update');

        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/{user}/attendances', [AdminUserController::class, 'userAttendances'])->name('users.attendances');

        Route::get('requests', [AdminAttendanceEditRequestController::class, 'index'])->name('admin.requests.index');
        Route::get('requests/{id}', [AdminAttendanceEditRequestController::class, 'show'])->name('requests.show');
        Route::post('requests/{id}', [AdminAttendanceEditRequestController::class, 'approve'])->name('requests.approve');
    });
});
