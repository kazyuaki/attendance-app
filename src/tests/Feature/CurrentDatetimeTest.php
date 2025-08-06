<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrentDatetimeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    public function testCurrentDatetimeIsDisplayedCorrectly()
    {
        /** @var \App\Models\User $user */

        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $now = \Carbon\Carbon::create(2025, 8, 6, 16, 20);
        Carbon::setTestNow($now);

        $date = $now->translatedFormat('Y年n月j日(D)');
        $time = $now->format('H:i');

        $response = $this->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee($date);
        $response->assertSee($time);
    }
}
