<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\FaceDescriptor;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FaceApiController extends Controller
{
    /**
     * Nhân viên đăng ký hoặc cập nhật khuôn mặt duy nhất của họ.
     */
    /**
     * Nhân viên đăng ký khuôn mặt VÀ cập nhật avatar.
     */
    public function registerFace(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|string',
            'avatar'     => 'required|string', // Nhận ảnh dưới dạng base64
        ]);
        
        $user = Auth::user();
        $employee = $user->employee;

        // 1. Xử lý và lưu ảnh avatar
        try {
            // Tách dữ liệu base64
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->avatar));
            // Tạo tên file duy nhất
            $filename = 'avatars/' . $employee->id . '_' . time() . '.jpg';
            
            // Xóa avatar cũ nếu có
            if ($employee->avatar && Storage::disk('public')->exists($employee->avatar)) {
                Storage::disk('public')->delete($employee->avatar);
            }

            // Đảm bảo thư mục avatars tồn tại
            if (!Storage::disk('public')->directoryExists('avatars')) {
                Storage::disk('public')->makeDirectory('avatars', 0777, true);
            }

            // Lưu file mới vào storage/app/public/avatars
            Storage::disk('public')->put($filename, $imageData);

            // Cập nhật đường dẫn avatar vào employee
            $employee->update(['avatar' => $filename]);

        } catch (\Exception $e) {
            Log::error('Lỗi lưu avatar: ' . $e->getMessage());
            // Trả về lỗi chi tiết để debug (Sẽ xóa sau khi sửa xong)
            return response()->json(['message' => 'Lỗi: ' . $e->getMessage()], 500);
        }

        // 2. Lưu face descriptor
        FaceDescriptor::updateOrCreate(
            ['user_id' => $user->id],
            ['descriptor' => $request->descriptor]
        );

        return response()->json([
            'message' => 'Đăng ký/Cập nhật khuôn mặt và avatar thành công!',
            'new_avatar_url' => Storage::url($employee->avatar), // Trả về URL mới để JS cập nhật
        ]);
    }


    /**
     * Lấy danh sách khuôn mặt đã đăng ký (mỗi người 1 khuôn mặt).
     */
    public function getFaceDescriptors()
    {
        $allDescriptors = FaceDescriptor::with('user.employee:id,first_name,last_name')->get();

        $dataForJs = $allDescriptors->map(function ($item) {
            $user = $item->user;
            if (!$user || !$user->employee) return null;

            return [
                'label'       => (string)$user->id,
                'name'        => $user->employee->full_name,
                'descriptors' => [json_decode($item->descriptor)], 
            ];
        })->filter()->values();

        return response()->json($dataForJs);
    }

    /**
     * Ghi nhận Check-in hoặc Check-out từ Kiosk, có tích hợp logic xác định trạng thái.
     */
    public function recordAttendance(Request $request)
{
    $validated = $request->validate([
        'user_id'   => 'required|exists:users,id',
        'otp_token' => 'required|string',
    ]);

    // --- Kiểm tra Token và Lấy Employee (giữ nguyên) ---
    $tokenKey = 'kiosk_otp_' . $validated['otp_token'];
    if (!Cache::has($tokenKey)) {
        return response()->json(['message' => 'Phiên chấm công không hợp lệ hoặc đã hết hạn.'], 403);
    }
    Cache::forget($tokenKey);
    $user = User::find($validated['user_id']);
    if (!$user || !$user->employee_id) {
        return response()->json(['message' => 'Tài khoản không được liên kết với nhân viên nào.'], 404);
    }
    
    $employee = $user->employee;
    $now = Carbon::now();
    $today = $now->copy()->startOfDay();

    $todaysAttendance = Attendance::where('employee_id', $employee->id)
        ->whereDate('date', $today->toDateString())
        ->first();

    // --- Xử lý Chấm Công (LOGIC MỚI THEO YÊU CẦU) ---
    if ($todaysAttendance) {
        // ĐÃ CÓ CHECK-IN -> Đây là các lần chấm tiếp theo trong ngày -> Luôn cập nhật CHECK-OUT
        // ─── Tính số giờ làm thực tế (C1) ────────────────────────────
        $checkInTime  = $todaysAttendance->check_in_time;
        $breakHours   = 0;
        $workSchedule = $employee->activeWorkScheduleOn($today);
        if ($workSchedule) {
            $breakHours = (float) ($workSchedule->break_hours ?? 0);
            // Legacy fallback: ca hành chính chưa set break_hours → 1h
            if ($breakHours === 0.0 && str_contains(strtolower($workSchedule->name ?? ''), 'hành chính')) {
                $breakHours = 1.0;
            }
        }
        $rawHours    = $checkInTime->diffInMinutes($now) / 60; // giờ thực tế check-in→check-out
        $actualHours = max(0, round($rawHours - $breakHours, 2));
        $actualHours = min($actualHours, 8.0); // Cap 8h/ngày (OT chưa tính)

        $todaysAttendance->check_out_time = $now;
        $todaysAttendance->actual_hours   = $actualHours;
        // ─────────────────────────────────────────────────────────────

        if ($workSchedule) {
            $endTime = Carbon::parse($today->toDateString() . ' ' . $workSchedule->end_time);
            
            // Xử lý nếu ca làm việc kéo qua đêm
            if ($endTime->lt(Carbon::parse($today->toDateString() . ' ' . $workSchedule->start_time))) {
                $endTime->addDay();
            }

            // Nếu về sớm hơn thời gian kết thúc -> Ghi chú lại (không hủy thành vắng mặt)
            if ($now->lt($endTime)) {
                $earlyMinutes = $now->diffInMinutes($endTime);
                if ($earlyMinutes > 5) {
                    $todaysAttendance->notes = ($todaysAttendance->notes ? $todaysAttendance->notes . " | " : "") . "Về sớm {$earlyMinutes} phút";
                }
            }
        }

        $todaysAttendance->save();
        Cache::forget($tokenKey); 
        $action = 'Check-out (Cập nhật)';
    } 
    else {
        // CHƯA CÓ CHECK-IN -> Lần đầu tiên chấm trong ngày -> Ghi nhận CHECK-IN
        $workSchedule = $employee->activeWorkScheduleOn($today);
        $status = 'present'; 
        
        if ($workSchedule) {
            $startTime = Carbon::parse($today->toDateString() . ' ' . $workSchedule->start_time);
            
            // Nếu đi trễ (bất kể bao lâu) -> Late (không nên đánh vắng mặt nếu người ta có đến)
            if ($now->gt($startTime)) {
                $status = 'late';
                $lateMinutes = $now->diffInMinutes($startTime);
                Log::info("FaceID Check-in: Employee {$employee->id} marked as LATE ({$lateMinutes} mins).");
            }
            // Đúng giờ hoặc sớm hơn
            else {
                $status = 'present';
                Log::info("FaceID Check-in: Employee {$employee->id} marked as PRESENT.");
            }
        } else {
            Log::warning("FaceID Check-in: Employee {$employee->id} has no schedule. Defaulted to 'present'.");
        }

        Attendance::create([
            'employee_id' => $employee->id,
            'date' => $today->toDateString(),
            'check_in_time' => $now,
            'status' => $status,
            'check_out_time' => null,
            'work_schedule_id' => $workSchedule?->id // Lưu ID ca làm việc tại thời điểm chấm công
        ]);
        Cache::forget($tokenKey); // Xóa token SAU KHI lưu DB thành công
        $action = 'Check-in';
    }
    
    return response()->json([
        'message' => "Chào mừng {$employee->full_name}! Ghi nhận {$action} lúc {$now->format('H:i:s')} thành công."
    ]);
}
     /**
     * Kiểm tra trạng thái đăng ký và trả về URL avatar.
     */
    public function getRegistrationStatus(Request $request)
    {
        $user = Auth::user();
        $hasFaceData = Auth::user()->faceDescriptors !== null;
        $avatarUrl = null;

        if ($user->employee && $user->employee->avatar) {
            // Lấy URL đầy đủ của avatar từ storage
            $avatarUrl = Storage::url($user->employee->avatar);
        }

        return response()->json([
            'has_face_data' => $hasFaceData,
            'avatar_url'    => $avatarUrl,
        ]);
    }
}