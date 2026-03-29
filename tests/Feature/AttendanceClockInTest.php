<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AttendanceClockInTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_clock_in_creates_attendance()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $this->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_time' => now()->format('H:i:s'),
            'status' => 'working',
        ]);
    }
}