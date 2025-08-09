<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceEditRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceEditRequestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    // ルートヘルパ：環境に合わせて片方を有効化
    private function indexUrl(array $query = []): string
    {
        // A) ルート名がある場合（推奨）
        if (app('router')->has('user.request.index')) {
            return route('user.request.index', $query);
        }
        // B) 直URLの場合（あなたの既存テストに合わせる）
        $q = $query ? ('?' . http_build_query($query)) : '';
        return '/stamp_correction_request/list' . $q;
    }

    public function testErrorWhenClockInAfterClockOut()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->post('stamp_correction_request',[
            'attendance_id' => $attendance->id,
            'clock_in' => '18:00',
            'clock_out' => '09:00',
            'breaks' => [
                ['start' => '10:00', 'end' => '11:00'],
            ],
            'note' => '退勤後に打刻してしまった',
        ]);

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function testErrorWhenBreakStartIsAfterClockOut()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'breaks' => [
                ['start' => '18:00', 'end' => '19:00'],
            ],
            'note' => '休憩時間を間違えました',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.start' => '休憩時間が不適切な値です',
        ]);
    }

    public function testErrorWhenBreakEndIsAfterClockOut()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'breaks' => [
                ['start' => '10:00', 'end' => '18:00'], 
            ],
            'note' => '休憩終了が遅すぎた',
        ]);

        $response->assertSessionHasErrors([
            'breaks.0.end' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function testErrorWhenNoteIsEmpty()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'note' => '', 
        ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }

    public function testEditRequestIsCreatedSuccessfully()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->post('/stamp_correction_request', [
            'attendance_id' => $attendance->id,
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
            'note' => 'テストの修正申請',
        ]);

        $this->assertDatabaseHas('attendance_edit_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'clock_in' => $attendance->work_date . ' 09:00:00',
            'clock_out' => $attendance->work_date . ' 17:00:00',
            'note' => 'テストの修正申請',
        ]);
    }

    public function testUserPendingRequestsAreDisplayed()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);

        // このユーザーの申請（承認待ち）
        AttendanceEditRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => 'pending',
        ]);

        AttendanceEditRequest::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/stamp_correction_request/list');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');

        $response->assertSeeInOrder([
            '承認待ち',
            $user->name,
            Carbon::parse($attendance->work_date)->format('Y/m/d'),
            '修正申請のテスト',
        ]);
    }


    public function testPendingTabDisplaysOnlyPendingRequests()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->for($user)->create();

        $pending = AttendanceEditRequest::factory()->count(2)
            ->for($user)->state(['attendance_id' => $attendance->id])
            ->sequence(['note' => 'P-001'], ['note' => 'P-002'])
            ->create(); // status: pending 既定

        AttendanceEditRequest::factory()->for($user)->state([
            'attendance_id' => $attendance->id,
            'status' => 'approved',
            'note' => 'A-001',
        ])->create();

        AttendanceEditRequest::factory()->for($user)->state([
            'attendance_id' => $attendance->id, 
            'status' => 'returned',
            'note' => 'R-001',
        ])->create();

        $response = $this->get($this->indexUrl(['status' => 'pending']));
        $response->assertOk();

        foreach ($pending as $request) {
            $response->assertSeeText($request->note);
        }
        $response->assertDontSeeText('A-001');
        $response->assertDontSeeText('R-001'); 
    }

    public function testDetailButtonLinksToAttendanceShowEachTab()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendancePending = Attendance::factory()->for($user)->create();
        $attendanceApproved = Attendance::factory()->for($user)->create();

        $attendanceEditRequestPending = AttendanceEditRequest::factory()->for($user)->state([
            'attendance_id' => $attendancePending->id,
            'status' => 'pending',
            'note' => 'P-DETAIL',
        ])->create();

        $attendanceEditRequestApproved = AttendanceEditRequest::factory()->for($user)->state([
            'attendance_id' => $attendanceApproved->id,
            'status' => 'approved',
            'note' => 'A-DETAIL',
        ])->create();
    
        $attendanceShowUrlPending = route('attendance.show',['id' => $attendanceEditRequestPending->attendance_id]);
        $attendanceShowUrlApproved = route('attendance.show',['id' => $attendanceEditRequestApproved->attendance_id]);

        $this->get($this->indexUrl(['status' => 'pending']))
            ->assertOk()
            ->assertSee($attendanceShowUrlPending);
        $this->get($this->indexUrl(['status' => 'approved']))
            ->assertOk()
            ->assertSee($attendanceShowUrlApproved);

        $this->get($attendanceShowUrlPending)->assertOk()->assertSee('勤怠詳細');
        $this->get($attendanceShowUrlApproved)->assertOk()->assertSee('勤怠詳細');
    }

}

