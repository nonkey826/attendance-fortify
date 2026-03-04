<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendance_change_requests', function (Blueprint $table) {
            $table->id();

            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // 申請内容（4項目）
            $table->date('requested_work_date')->nullable();
            $table->time('requested_clock_in_time')->nullable();
            $table->time('requested_clock_out_time')->nullable();

            // 休憩一覧： [{"start":"12:00","end":"12:30"}, ...]
            $table->json('requested_breaks')->nullable();

            $table->text('requested_note')->nullable();

            // 状態
            $table->string('status', 20)->default('pending'); // pending/approved/rejected

            // 管理者側処理用（後で承認機能で使う）
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_comment')->nullable();

            $table->timestamps();

            // 同一勤怠で pending を複数作らせない（仕様上ラクになる）
            $table->unique(['attendance_id', 'status'], 'att_req_unique_attendance_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_change_requests');
    }
};
