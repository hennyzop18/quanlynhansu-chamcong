<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsEmployee
{
    public function handle(Request $request, Closure $next): Response
    {

        // Kiểm tra đã đăng nhập VÀ có phải employee không
        if (!Auth::check() || !$request->user()->isEmployee()) {
            // abort(403, 'Unauthorized action.');
            return redirect('/dashboard')->with('error', 'Bạn không có quyền truy cập khu vực này.');
        }

        // Quan trọng: Đảm bảo user này có liên kết đến employee hợp lệ
        if (!Auth::user()->employee_id || !$request->user()->isActive()) {
            Auth::logout(); // Đăng xuất nếu user employee không có employee_id
            return redirect('/login')->with('error', 'Tài khoản nhân viên không hợp lệ.');
        }

        return $next($request);
    }
}