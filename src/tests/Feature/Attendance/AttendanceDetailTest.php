<?php

namespace Tests\Feature\Attendance;

use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDetailTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;
    public function testAttendanceDetailDisplaysCorrectUserName()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create([
            'name' => 'テスト太郎'
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $response = $this->get('attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('テスト太郎');
    }

    public function testAttendanceDetailDisplaysCorrectDate()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $carbon = Carbon::parse($attendance->work_date);

        $response->assertStatus(200);
        $response->assertSeeText($carbon->format('Y年'));
        $response->assertSeeText($carbon->format('n月j日'));
    }

    public function testAttendanceDetailDisplaysCLockInAndOutTimes()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:15:00',
            'clock_out' => '18:00:00',
            'work_date' => now()->format('Y-m-d'),
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('09:15');
        $response->assertSee('18:00');
    }

    public function testAttendanceDetailDisplaysBreakTimes()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
            'work_date' => now()->format('Y-m-d'),
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '12:00:00',
            'break_out' => '12:45:00',
        ]);

        BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_in' => '15:00:00',
            'break_out' => '15:15:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('12:00');
        $response->assertSee('12:45');
        $response->assertSee('15:00');
        $response->assertSee('15:15');
    }

}
