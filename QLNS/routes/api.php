<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FaceApiController;
use App\Http\Controllers\Api\KioskApiController;

// ... (có thể có các route API khác)

// API cho nhân viên đã đăng nhập để đăng ký khuôn mặt
Route::middleware('auth:sanctum')->post('/employee/face/register', [FaceApiController::class, 'registerFace']);

// --- NHÓM API CHO KIOSK ---
// Bảo vệ bằng middleware kiểm tra IP văn phòng
Route::middleware('verify.office.ip')->prefix('kiosk')->group(function () {
    
    // API cho Desktop App gọi để lấy token OTP
    Route::post('/request-token', [KioskApiController::class, 'requestToken']);

    // Trang Kiosk gửi vector khuôn mặt vừa quét, server tự nhận diện rồi ghi check-in/check-out.
    // (Đã bỏ API /face-descriptors: trước đây nó trả vector khuôn mặt của mọi nhân viên về trình duyệt.)
    Route::post('/record-attendance', [FaceApiController::class, 'recordAttendance'])
        ->middleware('throttle:120,1');
});