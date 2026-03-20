<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use App\Models\User;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceCorrectionRequestController;
use App\Http\Controllers\AttendanceCorrectionController; // ←追加

use App\Http\Requests\AdminLoginRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;

use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;

/*
|--------------------------------------------------------------------------
| ゲスト用ルート
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    Route::get('/admin/login', function () {
        return view('admin.login');
    })->name('admin.login');

    Route::redirect('/login/admin', '/admin/login');

    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    Route::post('/login', function (LoginRequest $request) {

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('attendance.index');
        }

        throw ValidationException::withMessages([
            'email' => ['ログイン情報が登録されていません'],
        ]);

    })->name('login.process');

    Route::post('/admin/login', function (AdminLoginRequest $request) {

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            if (auth()->user()->role !== 'admin') {

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'ログイン情報が登録されていません',
                ]);
            }

            return redirect()->route('admin.attendance.list');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

    })->name('admin.login.process');

    Route::post('/register', function (RegisterRequest $request) {

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
        ]);

        Auth::login($user);

        return redirect()->route('attendance.index');

    })->name('register.process');
});


/*
/*
|--------------------------------------------------------------------------
| 認証後ルート（一般ユーザー）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/', [AuthController::class, 'index'])->name('home');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');

    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');

    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');

    // 勤怠詳細（一般ユーザー）
    Route::get('/attendance/detail/{id}', [AttendanceListController::class, 'show'])
        ->name('attendance.detail');

    /*
    |--------------------------------------------------------------------------
    | 修正申請（一般ユーザー）
    |--------------------------------------------------------------------------
    */

    // 申請一覧
    Route::get('/stamp_correction_request/list',
        [AttendanceCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');

    // 申請詳細（※一般ユーザー用）
    Route::get('/stamp_correction_request/{attendance_correct_request}',
        [AttendanceCorrectionRequestController::class, 'show'])
        ->name('stamp_correction_request.show');

    // 修正申請送信
    Route::post('/stamp_correction_request/{attendance}',
        [AttendanceCorrectionController::class, 'store'])
        ->name('stamp_correction_request.store');
});


/*
|--------------------------------------------------------------------------
| 管理者ルート
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.list');

    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('admin.attendance.detail');

    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    Route::get('/admin/attendance/staff/{user}', [AdminAttendanceController::class, 'staffMonthly'])
        ->name('admin.attendance.staff.monthly');

    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
        ->name('admin.staff.list');

    /*
    |--------------------------------------------------------------------------
    | 修正申請（管理者）
    |--------------------------------------------------------------------------
    */

    // 申請一覧（管理者）
    Route::get('/admin/stamp_correction_request/list',
        [AttendanceCorrectionRequestController::class, 'adminIndex'])
        ->name('admin.stamp_correction_request.list'); 
    
    // 申請詳細（承認画面）
    Route::get('/admin/stamp_correction_request/{attendance_correct_request_id}',
        [AttendanceCorrectionRequestController::class, 'adminShow'])
        ->name('admin.stamp_correction_request.detail');

    // 承認処理（管理者のみ）
    Route::post('/admin/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AttendanceCorrectionRequestController::class, 'approve'])
        ->name('admin.stamp_correction_request.approve');
});
/*
|--------------------------------------------------------------------------
| ログアウト
|--------------------------------------------------------------------------
*/
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');