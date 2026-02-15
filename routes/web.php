<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;



Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [AuthController::class, 'index']);
});



/*
|--------------------------------------------------------------------------
| 一般ユーザールート
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/attendance', [AttendanceController::class, 'index']);

    Route::get('/attendance/list', [AttendanceController::class, 'list']);

    Route::get('/attendance/detail/{id}', function ($id) {
        return "attendance detail {$id}";
    });

    Route::get('/stamp_correction_request/list', function () {
        return 'stamp correction request list';
    });

    // 出勤
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);

    // 退勤
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);

    // 休憩開始
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart']);

    // 休憩終了
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd']);

});

/*
|--------------------------------------------------------------------------
| 管理者ルート（まだadmin制限は付けない）
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/attendance/list', function () {
        return 'admin attendance list';
    });

    Route::get('/admin/attendance/{id}', function ($id) {
        return "admin attendance {$id}";
    });

    Route::get('/admin/staff/list', function () {
        return 'admin staff list';
    });

    Route::get('/admin/attendance/staff/{attendance}', function ($attendance) {
        return "admin attendance staff {$attendance}";
    });

    Route::get('/admin/stamp_correction_request/list', function () {
        return 'admin stamp correction request list';
    });

});
