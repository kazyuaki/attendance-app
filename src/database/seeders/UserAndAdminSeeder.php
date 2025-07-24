<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AdminUser;

class UserAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者ユーザー
        AdminUser::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'), // 固定ログイン情報
        ]);

        // 一般ユーザー
        User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password'), // 固定ログイン情報
        ]);
    }
}