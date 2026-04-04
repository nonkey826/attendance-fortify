<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_correction_requests', function (Blueprint $table) {
            $table->text('requested_break_start_time')->nullable()->change();
            $table->text('requested_break_end_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendance_correction_requests', function (Blueprint $table) {
            $table->time('requested_break_start_time')->nullable()->change();
            $table->time('requested_break_end_time')->nullable()->change();
        });
    }
};