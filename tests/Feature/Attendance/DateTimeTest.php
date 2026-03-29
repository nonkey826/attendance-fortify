<?php

namespace Tests\Feature\Attendance;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class DateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 現在日時が表示される()
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 27, 10, 0, 0));

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get('/attendance');

       $response->assertSee(Carbon::now()->isoFormat('YYYY年M月D日（ddd）'));
    }
}