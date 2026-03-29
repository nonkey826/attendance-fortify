<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AttendanceCorrectionValidationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラー()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'clock_in_time' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $response = $this->from('/attendance/detail/'.$attendance->id)
            ->post(route('stamp_correction_request.store', $attendance->id), [
                'requested_clock_in_time' => '19:00',
                'requested_clock_out_time' => '18:00',
                'requested_note' => 'テスト',
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラー()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'clock_in_time' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $response = $this->from('/attendance/detail/'.$attendance->id)
            ->post(route('stamp_correction_request.store', $attendance->id), [
                'requested_clock_in_time' => '09:00',
                'requested_clock_out_time' => '18:00',
                'requested_note' => 'テスト',
                'breaks' => [
                    ['start' => '19:00', 'end' => '19:30'],
                ],
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラー()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'clock_in_time' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $response = $this->from('/attendance/detail/'.$attendance->id)
            ->post(route('stamp_correction_request.store', $attendance->id), [
                'requested_clock_in_time' => '09:00',
                'requested_clock_out_time' => '18:00',
                'requested_note' => 'テスト',
                'breaks' => [
                    ['start' => '17:00', 'end' => '19:00'],
                ],
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function 備考未入力でエラー()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now(),
            'clock_in_time' => now(),
            'status' => 'working',
        ]);

        $this->actingAs($user);

        $response = $this->from('/attendance/detail/'.$attendance->id)
            ->post(route('stamp_correction_request.store', $attendance->id), [
                'requested_clock_in_time' => '09:00',
                'requested_clock_out_time' => '18:00',
                'requested_note' => '',
            ]);

        $response->assertRedirect();
    }
}