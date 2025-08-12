<?php

namespace Tests\Feature\Auth;

use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Notifications\VerifyEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;


class EmailVerificationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /**
     * 便利ヘルパ：登録ページURL（なければ /register）
     */
    private function registerUrl(): string
    {
        return app('router')->has(('register') ? route('register') : '/register');
    }

    /**
     * 便利ヘルパ：認証誘導(Verify Notice) URL（なければ /email/verify）
     */
    private function verificationNoticeUrl(): string
    {
        return app('router')->has('verification.notice')
            ? route('verification.notice')
            : 'email/verify';
    }

    /**
     * 便利ヘルパ：勤怠登録画面URL（route が無ければ /attendance）
     */
    private function attendanceCreateUrl(): string
    {
        foreach(['attendance.create', 'attendances.create'] as $name) {
            if(app('router')->has($name)) {
                return route($name);
            }
        }
        return '/attendance';
    }

    /**
     * 指定テキストを含む<a ...>...</a>からhrefを抽出（最初の一致を返す）
     */
    private function extractHrefForCta(string $html, string $linkText): ?string
    {
        // 1) ボタンを<a>で実装しているケース
        $pattern = sprintf(
            '/<a[^>]*href="([^"]+)"[^>]*>[^<]*%s[^<]*<\/a>/u',
            preg_quote($linkText, '/')
        );
        if (preg_match($pattern, $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        }

        // 2) フォーム+ボタン（POST）で実装の場合はactionを見る（任意）
        if (preg_match('/<form[^>]*action="([^"]+)"[^>]*>.*?(認証はこちらから).*?<\/form>/us', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES, 'UTF-8');
        }

        return null;
    }

    /**
     * 同一オリジン（相対/同一ホスト）のときのみ実際にGETして検証する
     */
    private function isSameOriginLink(string $href): bool
    {
        // 相対パスならOK
        if (strpos($href, 'http://') !== 0 && strpos($href, 'https://') !== 0) {
            return true;
        }
        // テスト環境は http://localhost を想定（Laravelのテストクライアントは外部には行けない）
        return strpos($href, 'http://localhost') === 0;
    }

    /**
     * 会員登録後、認証メールが送信される
     */
    public function testVerificationEmailIsSentAfterRegistration(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        event(new Registered($user));

        Notification::assertSentTo($user, VerifyEmail::class);
    }


    public function testVerificationNoticeHasCtaBUttonAndNavigatesToVerificationPage(): void
    {
        // 未認証ユーザーでログイン
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['email_verified_at' => null]);
        $this->actingAs($user);

        // 誘導画面URL（環境に合わせて存在チェック）
        $noticeUrl = $this->verificationNoticeUrl();

        $response = $this->get($noticeUrl)->assertOk();
        $linkText = '認証はこちらから';
        $response->assertSee($linkText);

        $html = $response->getContent();
        $href = $this->extractHrefForCta($html, $linkText);
        $this->assertNotEmpty($href, 'CTAリンクのhrefが見つかりませんでした');

        // 同一ドメイン or 相対パスなら実際にGETして200系を期待
        if ($this->isSameOriginLink($href)) {
            $this->followingRedirects()->get($href)->assertSuccessful();
        }
        // 外部サイト（例: メールプロバイダ）なら遷移確認はスキップ（HTTPアクセスできないため）
    }
    
    public function testUserCanVerifyEmailAndIsRedirectedToAttendancePage(): void
    {
        Notification::fake();

        // 認証前のユーザー作成
        /** @var \App\Models\User $user */

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        // 認証リンク生成
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        // 認証リクエスト実行
        $response = $this->actingAs($user)->get($verificationUrl);
    
        $response->assertRedirect('/attendance');
        $this->assertNotNull($user->fresh()->email_verified_at);
        
    }
}

