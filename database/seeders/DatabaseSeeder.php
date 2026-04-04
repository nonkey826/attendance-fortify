<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者
User::firstOrCreate(
    ['email' => 'admin@test.com'],
    [
        'name' => 'admin',
        'password' => bcrypt('password'),
        'role' => 'admin'
    ]
);

// 一般ユーザー
$user = User::updateOrCreate(
    ['email' => 'user@test.com'],
    [
        'name' => 'user',
        'password' => bcrypt('12345678'),
        'role' => 'user'
    ]
);

        // 勤怠
        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
            'status' => 'done',
        ]);
    }
}