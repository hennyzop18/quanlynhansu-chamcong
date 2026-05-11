<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Import Auth

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra đã đăng nhập VÀ có phải admin không
        if (!Auth::check() || !$request->user()->isAdmin()) {
            return redirect('/dashboard')->with('error', 'Bạn không có quyền truy cập khu vực này.'); // Hoặc trang login
        }
        return $next($request);
    }
}