<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;

class AdminAttendanceUpdateTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_admin_can_update_break_time()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $admin->id,
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
        ]);

        $this->actingAs($admin);

        $this->post(route('admin.attendance.update', $attendance->id), [
            'requested_clock_in_time' => '09:00',
            'requested_clock_out_time' => '18:00',
            'requested_note' => 'テスト',
            'breaks' => [
                [
                    'start' => '12:00',
                    'end'   => '13:00',
                ]
            ],
        ]);

        // ← DBの中身を直接確認
        $exists = DB::table('breaks')
            ->where('attendance_id', $attendance->id)
            ->count();

        $this->assertTrue($exists >= 1);
    }
}