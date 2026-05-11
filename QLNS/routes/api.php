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

    // API cho trang Kiosk JS gọi để lấy dữ liệu các khuôn mặt
    Route::get('/face-descriptors', [FaceApiController::class, 'getFaceDescriptors']);

    // API cuối cùng để ghi nhận check-in/check-out
    Route::post('/record-attendance', [FaceApiController::class, 'recordAttendance']);
});