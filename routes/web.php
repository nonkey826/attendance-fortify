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

    // 一般ログイン画面
    Route::get('/login', function () {
        return view('auth.login');
    })->name('login');

    // 管理者ログイン画面
    Route::get('/admin/login', function () {
        return view('admin.login');
    })->name('admin.login');

    Route::redirect('/login/admin', '/admin/login')->middleware('guest');

    // 会員登録画面（一般ユーザー）
    Route::get('/register', function () {
        return view('auth.register');
    })->name('register');

    /*
    |--------------------------------------------------------------------------
    | 一般ユーザーログイン処理
    |--------------------------------------------------------------------------
    */
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

    /*
    |--------------------------------------------------------------------------
    | 管理者ログイン処理
    |--------------------------------------------------------------------------
    */
    Route::post('/admin/login', function (AdminLoginRequest $request) {

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {

            $request->session()->regenerate();

            // role が admin 以外ならログアウト
            if (auth()->user()->role !== 'admin') {

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'ログイン情報が登録されていません',
                ]);
            }

            // 管理者トップへ
            return redirect()->route('admin.attendance.list');
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);

    })->name('admin.login.process');

    /*
    |--------------------------------------------------------------------------
    | 一般ユーザー登録処理
    |--------------------------------------------------------------------------
    */
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
|--------------------------------------------------------------------------
| 認証後ルート（一般ユーザー）
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // トップ
    Route::get('/', [AuthController::class, 'index'])->name('home');

    // 打刻画面
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    // 勤怠一覧（月別）
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // 出勤
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');

    // 退勤
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    // 休憩開始
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');

    // 休憩終了
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');

    // 勤怠詳細（仕様書どおり）
    Route::get('/attendance/detail/{id}', [AttendanceListController::class, 'show'])
        ->name('attendance.detail');

    /*
    |--------------------------------------------------------------------------
    | 修正申請（一般ユーザー：送信だけ）
    |--------------------------------------------------------------------------
    | ※一覧（US014 管理者）は verified で弾かれるので管理者側へ移動
    */
    Route::post('/stamp_correction_request/{attendance}', [AttendanceCorrectionRequestController::class, 'store'])
        ->name('stamp_correction_request.store');
});


/*
|--------------------------------------------------------------------------
| 管理者ルート
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | 管理者：勤怠一覧
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.list');

    /*
    |--------------------------------------------------------------------------
    | 管理者：勤怠詳細
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('admin.attendance.detail');

    /*
    |--------------------------------------------------------------------------
    | 管理者：勤怠更新（PG09）
    |--------------------------------------------------------------------------
    */
    Route::post('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    /*
    |--------------------------------------------------------------------------
    | 管理者：スタッフ別月次勤怠
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/attendance/staff/{user}', [AdminAttendanceController::class, 'staffMonthly'])
        ->name('admin.attendance.staff.monthly');

    /*
    |--------------------------------------------------------------------------
    | 管理者：スタッフ一覧
    |--------------------------------------------------------------------------
    */
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
        ->name('admin.staff.list');

    /*
    |--------------------------------------------------------------------------
    | 修正申請：承認
    |--------------------------------------------------------------------------
    */
    Route::post(
        '/stamp_correction_request/approve/{attendance_correct_request_id}',
        [AttendanceCorrectionRequestController::class, 'approve']
    )->name('stamp_correction_request.approve');

    /*
    |--------------------------------------------------------------------------
    | 修正申請：一覧
    |--------------------------------------------------------------------------
    */
    Route::get('/stamp_correction_request/list', [AttendanceCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');

    /*
    |--------------------------------------------------------------------------
    | 修正申請：詳細
    |--------------------------------------------------------------------------
    */
    Route::get('/stamp_correction_request/{attendance_correct_request}', [AttendanceCorrectionRequestController::class, 'show'])
        ->name('stamp_correction_request.show');

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