<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // 未ログインは auth ミドルウェアに任せる
        if (!auth()->check()) {
            return redirect()->route('admin.login');
        }

        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        return $next($request);
    }
}