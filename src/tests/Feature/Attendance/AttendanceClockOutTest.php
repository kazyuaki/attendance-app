<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceClockOutTest extends TestCase
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

        $this->post('/attendance', ['action' => 'clock_in']);

        $response = $this->get('attendance');
        $response->assertSee('退勤');

        $this->post('/attendance', ['action' => 'clock_out']);

        $response = $this->get('attendance');
        $response->assertSee('退勤済');
    }

    public function testClockOutTimeIsShownInAttendanceList()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance', ['action' => 'clock_in']);

        $this->post('/attendance', ['action' => 'clock_out']);
        $clockOutTime = now()->format('H:i');

        $response = $this->get('/attendance');
        $response->assertSee($clockOutTime);
    }
}
