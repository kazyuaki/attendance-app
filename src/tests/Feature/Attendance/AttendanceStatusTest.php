<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function testStatusIsDisplayedAsOffDutyWhenUserHasNoAttendance() 
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function testStatusIsDisplayedAsWorkingWhenUserHasClockedIn() {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(2),
            'clock_out' => null,
            'work_date' => today()
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function testStatusIsDisplayedAsOnBreakWhenUserIsOnBreak()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(2),
            'clock_out' => null,
            'work_date' => today()
        ]);

        $attendance->breakTimes()->create([
            'break_in' => now()->subHours(),
            'break_out' => null,
        ]);

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function testStatusIsDisplayedAsClockedOutWhenUserHasClockedOut()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => now()->subHours(8),
            'clock_out' => now()->subHour(),
            'work_date' => today()
        ]);
        
        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
