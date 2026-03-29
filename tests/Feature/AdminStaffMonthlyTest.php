<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class AdminStaffMonthlyTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_指定ユーザーの勤怠のみ表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->get(
            route('admin.attendance.staff.monthly', ['user' => $user->id])
        );

        $response->assertStatus(200);
    }

    public function test_前月の勤怠が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->get(
            route('admin.attendance.staff.monthly', ['user' => $user->id]) .
            '?month=' . now()->subMonth()->format('Y-m')
        );

        $response->assertStatus(200);
    }

    public function test_翌月の勤怠が表示される()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user  = User::factory()->create();

        $this->actingAs($admin);

        $response = $this->get(
            route('admin.attendance.staff.monthly', ['user' => $user->id]) .
            '?month=' . now()->addMonth()->format('Y-m')
        );

        $response->assertStatus(200);
    }
}