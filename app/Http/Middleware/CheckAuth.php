<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Kalau belum ada session role, set sebagai viewer dulu
        // supaya bisa akses dashboard tanpa login
        if (!session('role')) {
            session(['role' => 'viewer']);
        }

        return $next($request);
    }
}