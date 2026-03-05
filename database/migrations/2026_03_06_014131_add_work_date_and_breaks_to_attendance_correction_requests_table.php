<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('attendance_correction_requests', function (Blueprint $table) {
        $table->date('requested_work_date')->nullable()->after('user_id');
        $table->time('requested_break_start_time')->nullable()->after('requested_clock_out_time');
        $table->time('requested_break_end_time')->nullable()->after('requested_break_start_time');
    });
}

public function down(): void
{
    Schema::table('attendance_correction_requests', function (Blueprint $table) {
        $table->dropColumn([
            'requested_work_date',
            'requested_break_start_time',
            'requested_break_end_time',
        ]);
    });
}
};
