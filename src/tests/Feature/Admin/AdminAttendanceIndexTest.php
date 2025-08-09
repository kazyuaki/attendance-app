<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceIndexTest extends TestCase
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
        // 「今日」を固定（タイムゾーンはアプリ設定に依存。必要なら ->setTimezone('Asia/Tokyo')）
        Carbon::setTestNow(Carbon::parse('2025-09-1 10:00:00'));
        // ログイン（管理者ガードがある前提。無い場合は通常ユーザーで代替）
        $this->loginAsAdminUser();
    }

    protected function loginAsAdminUser()
    {
        /** @var \App\Models\AdminUser $adminUser */
            $adminUser = AdminUser::factory()->create();
            $this->actingAs($adminUser, 'admin');
    }

    private function adminIndexUrl(array $query = []): string
    {
        if (app('router')->has('admin.attendances.index')) {
            return route('admin.attendances.index', $query);
        }

        $queryString = $query ? ('?' . http_build_query($query)) : '';
        return '/admin/attendances' . $queryString;
    }

    public function testInitialLoadDisplaysCurrentDate(): void
    {
        $today = Carbon::today();

        $response = $this->followingRedirects()
            ->get($this->adminIndexUrl());

        $response->assertOk();
        $response->assertSee($today->format('Y/m/d'));
    }
    
    public function testShowsAllUserAttendancesForSelectedDate() :void
    {
        $selectedDate = Carbon::parse('2025-9-1')->toDateString();

        $userA = User::factory()->create(['name' => 'User A']);
        $userB = User::factory()->create(['name' => 'User B']);
        $userC = User::factory()->create(['name' => 'User C']);


        Attendance::factory()->create([
            'user_id'   => $userA->id,
            'work_date' => $selectedDate,
            'clock_in'  => $selectedDate . ' 09:00:00',
            'clock_out' => $selectedDate . ' 17:00:00',
        ]);
        Attendance::factory()->create([
            'user_id'   => $userB->id,
            'work_date' => $selectedDate,
            'clock_in'  => $selectedDate . ' 10:00:00',
            'clock_out' => $selectedDate . ' 18:00:00',
        ]);
        Attendance::factory()->create([
            'user_id'   => $userC->id,
            'work_date' => $selectedDate,
            'clock_in'  => $selectedDate . ' 08:30:00',
            'clock_out' => $selectedDate . ' 16:30:00',
        ]);

        Attendance::factory()->create([
            'user_id'   => $userA->id,
            'work_date' => Carbon::parse($selectedDate)->subDay()->toDateString(),
        ]);

        $response = $this->get($this->adminIndexUrl(['date' => $selectedDate]));
        $response->assertOk();

        $response->assertSee(Carbon::parse($selectedDate)->format('Y/m/d'));

        $response->assertSee('User A');
        $response->assertSee('User B');
        $response->assertSee('User C');

        $response->assertSee('09:00');
        $response->assertSee('17:00');
        $response->assertSee('10:00');
        $response->assertSee('18:00');
        $response->assertSee('08:30');
        $response->assertSee('16:30');
    }

    public function testClickingPreviousDayShowsPreviousDateAttendances(): void
    {
        $today = Carbon::today();
        $previousDate = $today->copy()->subDay()->toDateString();

        $previousUser = User::factory()->create(['name' => 'Previous Day User']);
        Attendance::factory()->create([
            'user_id'   => $previousUser->id,
            'work_date' => $previousDate,
            'clock_in'  => $previousDate . ' 09:00:00',
            'clock_out' => $previousDate . ' 17:00:00',
        ]);

        $response = $this->get($this->adminIndexUrl(['date' => $previousDate]));
        $response->assertOk();

        $response->assertSee(Carbon::parse($previousDate)->format('Y/m/d'));

        $response->assertSee('Previous Day User');
        $response->assertSee('09:00');
        $response->assertSee('17:00');
    }


    public function testClickingNextDayShowsNextDateAttendances(): void
    {
        $today = Carbon::today();
        $nextDate = $today->copy()->addDay()->toDateString();

        $nextUser = User::factory()->create(['name' => 'Next Day User']);
        Attendance::factory()->create([
            'user_id'   => $nextUser->id,
            'work_date' => $nextDate,
            'clock_in'  => $nextDate . ' 11:00:00',
            'clock_out' => $nextDate . ' 19:00:00',
        ]);

        $response = $this->get($this->adminIndexUrl(['date' => $nextDate]));
        $response->assertOk();

        $response->assertSee(Carbon::parse($nextDate)->format('Y/m/d'));

        $response->assertSee('Next Day User');
        $response->assertSee('11:00');
        $response->assertSee('19:00');
    }
}
