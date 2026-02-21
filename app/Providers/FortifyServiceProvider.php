<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
{
    Fortify::createUsersUsing(CreateNewUser::class);

    Fortify::registerView(fn () => view('auth.register'));
    Fortify::loginView(fn () => view('auth.login'));

    Fortify::verifyEmailView(function () {
        return view('auth.verify-email');
    });

    RateLimiter::for('login', function (Request $request) {
        return Limit::perMinute(10)->by($request->email.$request->ip());
    });
}

}
