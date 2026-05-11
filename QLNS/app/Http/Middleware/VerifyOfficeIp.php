<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyOfficeIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    
    public function handle(Request $request, Closure $next)
    {
        $allowedIps = config('hr.allowed_office_ips', []);
        $requestIp = $request->ip();

        if (app()->isLocal()) {
            $allowedIps[] = '127.0.0.1';
        }

        Log::info('Verifying Office IP', [
            'request_ip' => $requestIp,
            'allowed_ips' => $allowedIps,
            'is_allowed' => in_array($requestIp, $allowedIps)
        ]);

        if (!in_array($requestIp, $allowedIps)) {
            Log::warning('IP Verification FAILED.', ['ip' => $requestIp]);
            return response()->json([
                'message' => 'Truy cập bị từ chối. Bạn không ở trong mạng văn phòng.',
                'ip_detected' => $requestIp
            ], 403);
        }

        Log::info('IP Verification PASSED.');
        return $next($request);
    }
}