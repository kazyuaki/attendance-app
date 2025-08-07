<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function testUserCanSeeOwnAttendanceRecords()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $dates = [
            now()->format('Y-m-d'),
            now()->subDay()->format('Y-m-d'),
            now()->subDays(2)->format('Y-m-d'),
        ];

        $attendances = collect();

        foreach ($dates as $date) {
            $attendances->push(Attendance::factory()->create([
                'user_id' => $user->id,
                'work_date' => $date,
                'clock_in' => '09:00:00',
                'clock_out' => '17:00:00',
            ]));
        }

        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '17:00:00',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/list');

        foreach ($attendances as $attendance) {
            $formattedDate = Carbon::parse($attendance->work_date)->format('m/d');
            $response->assertSee($formattedDate);
        }

        $otherDisplayDate = now()->subMonth()->startOfMonth()->format('m/d');
        $response->assertDontSee($otherDisplayDate);
    }

    public function testCurrentMonthIsDisplayedOnAttendanceList()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('attendance/list');

        $currentMonth = now()->format('Y/m');

        $response->assertSee($currentMonth);
    }

    public function testPreviousMonthIsDisplayedWhenNavigated()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $prevMonth = now()->subMonth()->format('Y-m');

        $response = $this->get('/attendance/list?date=' . $prevMonth);

        $response->assertStatus(200);
        $response->assertSee(now()->subMonth()->format('Y/m'));
    }

    public function testNextMonthIsDisplayedWhenNavigated()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $nextMonth = now()->addMonth()->format('Y-m');

        $response = $this->get('/attendance/list?date=' . $nextMonth);

        $response->assertStatus(200);
        $response->assertSee(now()->addMonth()->format('Y/m')); 
    }

    public function testDetailButtonNavigatesToAttendanceDetailPage()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $this->actingAs($user);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->format('Y-m-d'),
            'clock_in' => '09:00:00',
            'clock_out' => '18:00:00',
        ]);

        $response = $this->get('attendance/list');

        $detailUrl = '/attendance/detail/' . $attendance->id;

        $response->assertSee($detailUrl);

        $detailResponse = $this->get($detailUrl);
        $detailResponse->assertStatus(200);

        $detailResponse->assertSee('09:00');
        $detailResponse->assertSee('18:00');
    }
}
