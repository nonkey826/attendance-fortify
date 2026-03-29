<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceCorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_承認でステータスが変わる()
    {
       

// 管理者ユーザー
$user = User::factory()->create([
    'role' => 'admin'
]);

$this->actingAs($user);

// 勤怠
$attendance = Attendance::create([
    'user_id' => $user->id,
    'work_date' => now(),
    'clock_in_time' => now(),
    'clock_out_time' => now(),
    'status' => 'working',
]);

// 修正申請
$request = AttendanceCorrectionRequest::create([
    'attendance_id' => $attendance->id,
    'user_id' => $user->id,
    'status' => 'pending',
    'requested_clock_in_time' => now(),
    'requested_clock_out_time' => now(),
    'requested_breaks' => json_encode([
        ['start' => '12:00', 'end' => '13:00']
    ]),
    'requested_note' => 'テスト',
]);

// ✅ここでPOST
$this->post("/stamp_correction_request/approve/{$request->id}");

        // 確認
        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',

        ]);
    }
}