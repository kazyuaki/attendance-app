<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function testBreakButtonFunctionsCorrectly()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/attendance', ['action' => 'clock_in']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/break/start', ['action' => 'break_in']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    public function testUserCanTakeMultipleBreaksInOneDay()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post('/attendance', ['action' => 'clock_in']);

        $this->post('/break/start', ['action' => 'break_in']);
        $this->post('/break/end', ['action' => 'break_out']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');

        $this->post('/break/start', ['action' => 'break_in']);
        $this->post('/break/end', ['action' => 'break_out']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩入');
    }

    public function testBreakEndButtonFunctionsCorrectly()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance', ['action' => 'clock_in']);

        $this->post('/break/start', ['action' => 'break_in']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->post('/break/end', ['action' => 'break_out']);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    public function testUserCanEncBreakMultipleTimesInOneDay()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance', ['action' => 'clock_in']);

        $this->post('/break/start', ['action' => 'break_in']);
        $this->post('/break/end', ['action' => 'break_out']);

        $this->post('/break/start', ['action' => 'break_in']);

        $response = $this->get('/attendance');
        $response->assertSee('休憩戻');

        $this->post('/break/end', ['action' => 'break_out']);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }


    public function testBreakTimeAreShownInAttendanceList()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/attendance', ['action' => 'clock_in']);

        $this->post('/break/start', ['action' => 'break_in']);
        $breakStart = now()->copy();

        $this->post('/break/end', ['action' => 'break_out']);
        $breakEnd = now()->copy();

        $breakMinutes = $breakEnd->diffInMinutes($breakStart);
        $breakFormatted = sprintf('%02d:%02d', floor($breakMinutes / 60), $breakMinutes % 60);

        $response = $this->get('/attendance/list');

        $response->assertSee($breakFormatted);
    }
}
