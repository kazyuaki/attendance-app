<?php

namespace Tests\Feature\Admin;

use App\Models\AdminUser;
use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminEditRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdminUser();
    }

    protected function loginAsAdminUser(): void
    {
        /** @var \App\Models\AdminUser $admin */
        $admin = AdminUser::factory()->create();
        $this->actingAs($admin, 'admin');
    }

    /**
     * 修正申請一覧ページURL
     */
    private function adminRequestIndexUrl(array $query = []): string
    {
        $qs = $query ? ('?' . http_build_query($query)) : '';
        return "/admin/requests{$qs}";
    }

    /**
     * 修正申請詳細ページURL
     */
    private function adminRequestShowUrl(AttendanceEditRequest $request): string
    {
        return "/admin/requests/{$request->id}";
    }

    /**
     * 修正申請承認URL
     */
    private function adminRequestApproveUrl(AttendanceEditRequest $request): string
    {
        return "/admin/requests/{$request->id}";
    }

    /**
     * 承認待ちの修正申請が全て表示されている
     */
    public function testPendingRequestsAreDisplayed(): void
    {
        $users = User::factory()->count(2)->create();

        foreach ($users as $index => $user) {
            AttendanceEditRequest::factory()->create([
                'user_id' => $user->id,
                'status' => 'pending',
            ]);
        }

        $response = $this->get($this->adminRequestIndexUrl(['status' => 'pending']));
        $response->assertOk();

        foreach ($users as $index => $user) {
            $response->assertSee($user->name);
        }
    }

    /**
     * 承認済みの修正申請が全て表示されている
     */
    public function testApprovedRequestsAreDisplayed(): void
    {
        $users = User::factory()->count(3)->create();

        foreach ($users as $index => $user) {
            AttendanceEditRequest::factory()->create([
                'user_id' => $user->id,
                'status' => 'approved',
            ]);
        }

        $response = $this->get($this->adminRequestIndexUrl(['status' => 'approved']));
        $response->assertOk();

        foreach ($users as $index => $user) {
            $response->assertSee($user->name);
        }
    }

    /**
     * 修正申請の詳細内容が正しく表示されている
     */
    public function testRequestDetailIsDisplayedCorrectly(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        $request = AttendanceEditRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'note' => '遅刻理由: 電車遅延',
        ]);

        $response = $this->get($this->adminRequestShowUrl($request));
        $response->assertOk()
            ->assertSee($user->name)
            ->assertSee('遅刻理由: 電車遅延');
    }

    /**
     * 修正申請の承認処理が正しく行われる
     */
    public function testApproveRequestUpdatesAttendance(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $request = AttendanceEditRequest::factory()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'clock_in'      => $attendance->work_date . ' 10:00:00',
            'clock_out'     => $attendance->work_date . ' 19:00:00',
        ]);

        $response = $this->post($this->adminRequestApproveUrl($request));

        $response->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in' => '10:00:00',
            'clock_out' => '19:00:00',
        ]);

        $this->assertDatabaseHas('attendance_edit_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }
}
