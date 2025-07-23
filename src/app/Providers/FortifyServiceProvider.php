<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Requests\LoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // ユーザー操作のバインディング
        Fortify::createUsersUsing(CreateNewUser::class);

        /**
         * 
         * ✅ ログイン画面をURLによって出し分ける
         */
        Fortify::loginView(function () {
            return request()->is('admin/*')
                ? view('admin.auth.login')
                : view('auth.login');
        });

        /**
         * ✅ 認証ロジックの分岐（ガードの切り替え）
         */
        Fortify::authenticateUsing(function (Request $request) {
            app(LoginRequest::class)->validateResolved();

            // 管理者ログインと一般ユーザーログインを分ける
            if (request()->is('admin/*')) {
                $admin = \App\Models\AdminUser::where('email', $request->email)->first();

                if ($admin && Hash::check($request->password, $admin->password)) {
                    Auth::guard('admin')->login($admin);
                    return $admin;
                }
            } else {
                $user = \App\Models\User::where('email', $request->email)->first();

                if ($user && Hash::check($request->password, $user->password)) {
                    return $user;
                }
            }

            return null;
        });


        Fortify::redirects('login', '/attendance');
        Fortify::redirects('register', '/login');

        /**
         * ✅ ログイン制限
         */
        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(
                Str::lower($request->input(Fortify::username())) . '|' . $request->ip()
            );

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
