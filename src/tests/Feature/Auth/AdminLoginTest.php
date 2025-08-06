<?php

namespace Tests\Feature\Auth;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function testEmailIsRequired()
    {
        $admin = AdminUser::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123'
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function testPasswordIsRequired()
    {
        $admin = AdminUser::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'test@example.com',
            'password' => ''
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function testInvalidLoginCredentialsReturnError()
    {
        $admin = AdminUser::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wron@example.com',
            'password' => 'password123'
        ]);

        $response->assertSessionHas('error', 'ログイン情報が登録されていません');
    }
}
