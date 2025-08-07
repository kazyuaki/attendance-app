<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function testClockInButtonFunctionsCorrectly()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/attendance',['action' => 'clock_in']);
        $response->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function testClockInButtonIsNotVisibleAfterClockOut()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now()->subHour(),
        ]);

        $response = $this->get('/attendance');

        $response->assertDontSee('出勤');
    }

    public function testClockInTimeIsVisibleInAttendanceList()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance', ['action' => 'clock_in']);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();


        $response= $this->get('/attendance/list');

        $response->assertSee(Carbon::parse($attendance->work_date)->isoFormat('MM/DD(dd)'));
        $response->assertSee(Carbon::parse($attendance->clock_in)->format('H:i'));
    }
}
