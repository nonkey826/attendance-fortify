<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 管理者
        $admin = User::firstOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'admin',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        // 管理者ユーザーを作成した後に、メール認証を完了させます
        $admin->email_verified_at = now();
        $admin->save();

        // 一般ユーザー
        $user = User::updateOrCreate(
            ['email' => 'user@test.com'],
            [
                'name' => 'user',
                'password' => bcrypt('12345678'),
                'role' => 'user'
            ]
        );

        // ランダムな日付を生成（過去30日以内の日付）
        $randomDate = Carbon::now()->subDays(rand(1, 30))->toDateString();

        // 重複を避けるために firstOrCreate を使用
        Attendance::firstOrCreate([
            'user_id' => $user->id,
            'work_date' => $randomDate, // ランダムな日付を設定
        ], [
            'clock_in_time' => '09:00:00',
            'clock_out_time' => '18:00:00',
            'status' => 'done',
        ]);
    }
}