<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AttendanceDetailTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_attendance_detail_displays_correct_data()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'テスト太郎',
        ]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-03-01',
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
            'status' => 'finished',
        ]);

        $this->actingAs($user);

        $response = $this->get('/attendance/detail/' . $attendance->id);

        $response->assertSee('テスト太郎');
        $response->assertSee('2026-03-01');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
