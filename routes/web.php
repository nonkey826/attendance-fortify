<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| ゲスト用ルート
|--------------------------------------------------------------------------
*/

// ログイン画面
Route::get('/login', function () {
    return view('auth.login');
})->middleware('guest')->name('login');

// 会員登録画面
Route::get('/register', function () {
    return view('auth.register');
})->middleware('guest')->name('register');

// ログイン処理
Route::post('/login', function (LoginRequest $request) {

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->route('attendance.index');
    }

    throw ValidationException::withMessages([
        'email' => ['ログイン情報が登録されていません'],
    ]);

})->middleware('guest')->name('login.process');

// 会員登録処理
Route::post('/register', function (RegisterRequest $request) {

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    Auth::login($user);

    return redirect()->route('attendance.index');

})->middleware('guest')->name('register.process');


/*
|--------------------------------------------------------------------------
| 認証後ルート（一般ユーザー）
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    // トップ
    Route::get('/', [AuthController::class, 'index'])
        ->name('home');

    /*
    |--------------------------------------------------------------------------
    | 勤怠登録画面
    |--------------------------------------------------------------------------
    */

    // 打刻画面
    Route::get('/attendance', [AttendanceController::class, 'index'])
        ->name('attendance.index');

    // 月別一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])
        ->name('attendance.list');

    // 出勤
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
        ->name('attendance.clockIn');

    // 退勤
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
        ->name('attendance.clockOut');

    // 休憩開始
    Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])
        ->name('attendance.breakStart');

    // 休憩終了
    Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])
        ->name('attendance.breakEnd');
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


/*
|--------------------------------------------------------------------------
| 管理者ルート
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    Route::get('/admin/attendance/list', function () {
        return 'admin attendance list';
    })->name('admin.attendance.list');

    Route::get('/admin/attendance/{id}', function ($id) {
        return "admin attendance {$id}";
    })->name('admin.attendance.detail');

});



