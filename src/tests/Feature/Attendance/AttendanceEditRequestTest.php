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

}