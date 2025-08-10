<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminUsersAndMonthlyAttendancesTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdminUser();
    }

    protected function loginAsAdminUser()
    {
        /** @var \App\Models\AdminUser $adminUser */
        $adminUser = AdminUser::factory()->create();
        $this->actingAs($adminUser, 'admin'); 
    }

    /**
     * スタッフ一覧URL（実装に合わせてA/B切替）
     */
    private function adminUsersIndexUrl(array $query = []): string
    {
        if (app('router')->has('admin.users.index')) {
            return route('admin.users.index', $query);
        }
        $queryString = $query ? ('?' . http_build_query($query)) : '';
        return '/admin/users' . $queryString;
    }

    /**
     * ユーザー別 月次勤怠一覧URL（実装に合わせてA/B切替）
     * 例: /admin/users/{user}/attendances?month=YYYY-MM
     */
    private function adminUserMonthlyAttendancesUrl(User $user, array $query = []): string
    {

        $queryString = $query ? ('?' . http_build_query($query)) : '';
        return "/admin/users/{$user->id}/attendances{$queryString}";
    }

    /**
     * 勤怠詳細URL（実装に合わせてA/B切替）
     */
    private function adminAttendanceShowUrl(Attendance $attendance): string
    {

        return "/admin/attendances/{$attendance->id}";
    }

    public function testAdminCanSeeAllUsersNameAndEmailOnStaffList()
    {
        $userA = User::factory()->create(['name' => 'User A', 'email' => 'a@example.com']);
        $userB = User::factory()->create(['name' => 'User B', 'email' => 'b@example.com']);
        $userC = User::factory()->create(['name' => 'User C', 'email' => 'c@example.com']);

        $response = $this->followingRedirects()->get($this->adminUsersIndexUrl());

        $response->assertOk();
        $response->assertSee('User A')->assertSee('a@example.com');
        $response->assertSee('User B')->assertSee('b@example.com');
        $response->assertSee('User C')->assertSee('c@example.com');
    }

    public function testMonthlyAttendanceListShowsSelectedUsersAttendanceAccurately()
    {
        /** @var \App\Models\User $targetUser */
        $targetUser = User::factory()->create(['name' => 'Monthly Target User']);

        $month = '2025-09';
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $datesInMonth = [
            $monthStart->copy()->day(1),
            $monthStart->copy()->day(15),
            $monthStart->copy()->day(28),
        ];

        foreach($datesInMonth as $workDate) {
            Attendance::factory()->create([
                'user_id'   => $targetUser->id,
                'work_date' => $workDate->toDateString(),
                'clock_in'  => $workDate->toDateString() . ' 09:00:00',
                'clock_out' => $workDate->toDateString() . ' 17:00:00',
            ]);
        }

        // 前月・翌月にノイズ
        Attendance::factory()->create([
            'user_id'   => $targetUser->id,
            'work_date' => $monthStart->copy()->subMonth()->day(1)->toDateString(),
        ]);
        Attendance::factory()->create([
            'user_id'   => $targetUser->id,
            'work_date' => $monthStart->copy()->addMonth()->day(1)->toDateString(),
        ]);

        // クエリキーは実装に合わせて
        $response = $this->get($this->adminUserMonthlyAttendancesUrl($targetUser, ['date' => $month]));
        $response->assertOk();
        $response->assertSee($monthStart->format('Y/m'));
        $response->assertSee('Monthly Target Userさんの勤怠');

        foreach($datesInMonth as $workDate)
        {
            $response->assertSee($workDate->format('m/d'));
            $response->assertSee('09:00');
            $response->assertSee('17:00');
        }
    }

    public function testClickingPreviousMonthShowsPreviousMonthAttendances() :void
    {
        $targetUser = User::factory()->create(['name' => 'Prev Month User']);

        $baseMonth = '2025-08';
        $previousMonth =  Carbon::parse($baseMonth . '-01')->subMonth();

        // 前月に1件
        Attendance::factory()->create([
            'user_id'   => $targetUser->id,
            'work_date' => $previousMonth->copy()->day(10)->toDateString(),
            'clock_in'  => $previousMonth->copy()->day(10)->toDateString() . ' 10:00:00',
            'clock_out' => $previousMonth->copy()->day(10)->toDateString() . ' 18:00:00',
        ]);

        // 画面の「前月」ボタンのリンク先URLと同等の GET（
        $response = $this->get($this->adminUserMonthlyAttendancesUrl($targetUser, [
            'date' => $previousMonth->format('Y-m'),
        ]));

        $response->assertOk();
        $response->assertSee($previousMonth->format('Y/m'));

        $response->assertSee('Prev Month User');
        $response->assertSee('10:00');
        $response->assertSee('18:00');
    }


    public function testClickingNextMonthShowsNextMonthAttendances(): void
    {
        $targetUser = User::factory()->create(['name' => 'Next Month User']);

        $baseMonth = '2025-08';
        $nextMonth = Carbon::parse($baseMonth . '-01')->addMonth();

        // 翌月に1件
        Attendance::factory()->create([
            'user_id'   => $targetUser->id,
            'work_date' => $nextMonth->copy()->day(5)->toDateString(),
            'clock_in'  => $nextMonth->copy()->day(5)->toDateString() . ' 11:00:00',
            'clock_out' => $nextMonth->copy()->day(5)->toDateString() . ' 19:00:00',
        ]);

        // 画面の「翌月」ボタンのリンク先URLと同等の GET
        $response = $this->get($this->adminUserMonthlyAttendancesUrl($targetUser, [
            'date' => $nextMonth->format('Y-m'),
        ]));

        $response->assertOk();
        $response->assertSee($nextMonth->format('Y/m'));

        $response->assertSee('Next Month User');
        $response->assertSee('11:00');
        $response->assertSee('19:00');
    }

    public function testClickingDetailNavigatesToThatDaysAttendanceDetail(): void
    {
        $targetUser = User::factory()->create(['name' => 'Detail Link User']);

        $month = '2025-06';
        $workDate = Carbon::parse($month . '-12');

        /** @var \App\Models\Attendance $attendance */
        $attendance = Attendance::factory()->create([
            'user_id'   => $targetUser->id,
            'work_date' => $workDate->toDateString(),
            'clock_in'  => $workDate->toDateString() . ' 09:30:00',
            'clock_out' => $workDate->toDateString() . ' 18:30:00',
        ]);

        // 月次一覧に「詳細」リンク（= 勤怠詳細URL）がレンダリングされていることを確認
        $attendanceShowUrl = $this->adminAttendanceShowUrl($attendance);

        $this->get($this->adminUserMonthlyAttendancesUrl($targetUser, ['date' => $month]))
            ->assertOk()
            ->assertSee($attendanceShowUrl); 

        $response = $this->get($attendanceShowUrl)->assertOk();
        $response->assertSee('勤怠詳細');
        $response->assertSee('Detail Link User');
        $response->assertSee($workDate->format('Y年'));
        $response->assertSee($workDate->format('n月j日'));
        $response->assertSee('value="09:30"', false);
        $response->assertSee('value="18:30"', false);
    }

}
