<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AttendanceClockOutTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_clock_out_sets_status_to_finished()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        // 出勤
        $this->post('/attendance/clock-in');

        // 退勤
        $this->post('/attendance/clock-out');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'status' => 'finished',
        ]);
    }
}