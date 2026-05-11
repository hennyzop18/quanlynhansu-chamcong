<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class KioskApiController extends Controller
{
    /**
     * Xử lý yêu cầu cấp token từ ứng dụng Desktop Kiosk.
     * Middleware 'verify.office.ip' đã chạy trước khi vào hàm này.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestToken(Request $request)
    {
        // 1. Validate dữ liệu gửi lên từ Desktop App
        $validated = $request->validate([
            'ssid' => 'required|string',
            // 'mac_address' => 'nullable|string', // Tùy chọn: có thể thêm xác thực MAC
        ]);

        $receivedSsid = trim($validated['ssid']);
        $allowedSsids = array_map('trim', config('hr.allowed_office_ssids', [])); 

        // 2. Kiểm tra (không phân biệt hoa thường và bỏ qua khoảng trắng thừa)
        $isAllowed = false;
        foreach ($allowedSsids as $allowed) {
            if (strcasecmp($receivedSsid, $allowed) === 0) {
                $isAllowed = true;
                break;
            }
        }

        if (!$isAllowed) {
            Log::warning('Kiosk token request denied: Invalid SSID.', [
                'received_ssid' => $receivedSsid,
                'allowed_ssids' => $allowedSsids,
                'request_ip' => $request->ip(),
            ]);
            
            return response()->json([
                'message' => 'Yêu cầu bị từ chối. Thiết bị không kết nối đúng mạng Wi-Fi của văn phòng.',
            ], 403);
        }

        // 3. Nếu mọi thứ hợp lệ, tạo và trả về token
        $token = Str::random(40);
        $cacheKey = 'kiosk_otp_' . $token;
        
        // Lưu token vào cache, hiệu lực 120 giây (đủ thời gian tải model AI và nhận diện)
        Cache::put($cacheKey, true, now()->addSeconds(120));

        Log::info('Kiosk token granted successfully.', [
            'ssid' => $receivedSsid,
            'request_ip' => $request->ip(),
        ]);

        return response()->json(['otp_token' => $token]);
    }
}