<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceShowUpdateTest extends TestCase
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

    protected function loginAsAdminUser(): void
    {
        /** @var \App\Models\AdminUser $adminUser */
        $adminUser = AdminUser::factory()->create();
        $this->actingAs($adminUser, 'admin');
    }

    private function adminAttendanceShowUrl(Attendance $attendance): string
    {
        if(app('router')->has('admin.attendance.show')) {
            return route('admin.attendances.show', ['attendance' => $attendance->id]);
        }
        return "/admin/attendances/{$attendance->id}";
    }

    private function adminAttendanceUpdateUrl(Attendance $attendance) : string
    {
        if (app('router')->has('admin.attendance.update')) {
            return route('admin.attendances.update', ['attendance' => $attendance->id]);
         }
    return "/admin/attendances/{$attendance->id}";
    }

    public function testShowDisplaySelectedAtendanceDetails(): void
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['name' => 'Detail Target User']);

        $workDate = Carbon::parse('2025-01-20')->toDateString();

        /** @var \App\Models\Attendance $attendance */
        $attendance = Attendance::factory()->create([
            'user_id'   => $user->id,
            'work_date' => $workDate,
            'clock_in'  => $workDate . ' 09:15:00',
            'clock_out' => $workDate . ' 18:45:00',
        ]);

        $response = $this->get($this->adminAttendanceShowUrl($attendance));
        $response->assertOk();

        $response->assertSee('勤怠詳細');
        $response->assertSee('Detail Target User');
        $response->assertSee(Carbon::parse($workDate)->format('Y年'));
        $response->assertSee(Carbon::parse($workDate)->format('n月j日'));

        $response->assertSee('09:15');
        $response->assertSee('18:45');
    }
}
