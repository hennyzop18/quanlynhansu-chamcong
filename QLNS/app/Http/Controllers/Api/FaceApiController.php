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
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FaceApiController extends Controller
{
    /** Số chiều của vector khuôn mặt do face-api.js tạo ra. */
    private const DESCRIPTOR_LENGTH = 128;

    /**
     * Nhân viên đăng ký khuôn mặt VÀ cập nhật avatar.
     */
    public function registerFace(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|string',
            'avatar'     => 'required|string', // Nhận ảnh dưới dạng base64
        ]);

        // Descriptor phải là JSON mảng 128 số, nếu không server sẽ không so khớp được
        $vector = json_decode($request->descriptor, true);
        if (!$this->isValidDescriptor($vector)) {
            return response()->json(['message' => 'Dữ liệu khuôn mặt không hợp lệ. Vui lòng đăng ký lại.'], 422);
        }

        $user = Auth::user();
        $employee = $user->employee;

        // 1. Xử lý và lưu ảnh avatar
        try {
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->avatar));
            $filename = 'avatars/' . $employee->id . '_' . time() . '.jpg';

            // Xóa avatar cũ nếu có
            if ($employee->avatar && Storage::disk('public')->exists($employee->avatar)) {
                Storage::disk('public')->delete($employee->avatar);
            }

            if (!Storage::disk('public')->directoryExists('avatars')) {
                Storage::disk('public')->makeDirectory('avatars', 0777, true);
            }

            Storage::disk('public')->put($filename, $imageData);
            $employee->update(['avatar' => $filename]);
        } catch (\Exception $e) {
            Log::error('Lỗi lưu avatar: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi lưu ảnh đại diện: ' . $e->getMessage()], 500);
        }

        // 2. Lưu face descriptor (chuẩn hóa về mảng số)
        FaceDescriptor::updateOrCreate(
            ['user_id' => $user->id],
            ['descriptor' => json_encode(array_map('floatval', array_values($vector)))]
        );

        return response()->json([
            'message' => 'Đăng ký/Cập nhật khuôn mặt và avatar thành công!',
            'new_avatar_url' => Storage::url($employee->avatar),
        ]);
    }

    /**
     * Ghi nhận Check-in / Check-out từ Kiosk.
     *
     * Trình duyệt Kiosk chỉ gửi vector khuôn mặt vừa quét. Server tự so với vector
     * đã đăng ký của mọi nhân viên để xác định là ai, nên client không thể tự chọn
     * user_id để chấm hộ người khác.
     */
    public function recordAttendance(Request $request)
    {
        $validated = $request->validate([
            'otp_token'    => 'required|string',
            'descriptor'   => 'required|array|size:' . self::DESCRIPTOR_LENGTH,
            'descriptor.*' => 'required|numeric',
        ]);

        // --- 1. Kiểm tra phiên Kiosk (token do Kiosk-Helper-App xin, dùng lại được tới khi hết hạn) ---
        $tokenKey = 'kiosk_otp_' . $validated['otp_token'];
        if (!Cache::has($tokenKey)) {
            return response()->json([
                'message' => 'Phiên chấm công đã hết hạn. Vui lòng mở lại từ ứng dụng Kiosk.',
                'session_expired' => true,
            ], 403);
        }

        // --- 2. Server tự nhận diện khuôn mặt ---
        $match = $this->findBestMatch(array_values($validated['descriptor']));
        if (!$match) {
            return response()->json([
                'matched' => false,
                'message' => 'Không nhận ra khuôn mặt. Vui lòng nhìn thẳng vào camera.',
            ], 404);
        }
        [$user, $distance] = $match;

        if (!$user->employee_id || !$user->employee) {
            return response()->json(['message' => 'Tài khoản không được liên kết với nhân viên nào.'], 404);
        }
        $employee = $user->employee;
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();

        // --- 3. Chống chấm trùng: camera quét liên tục nên cùng một người sẽ bị nhận diện nhiều lần ---
        $recentKey = 'kiosk_recent_' . $employee->id;
        if ($lastTime = Cache::get($recentKey)) {
            return response()->json([
                'matched'   => true,
                'duplicate' => true,
                'name'      => $employee->full_name,
                'distance'  => round($distance, 3),
                'message'   => "{$employee->full_name} đã chấm công lúc {$lastTime}.",
            ]);
        }

        $todaysAttendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $today->toDateString())
            ->first();

        if ($todaysAttendance) {
            // ĐÃ CÓ CHECK-IN -> các lần quét sau trong ngày luôn cập nhật CHECK-OUT
            $checkInTime  = $todaysAttendance->check_in_time;
            $breakHours   = 0;
            $workSchedule = $employee->activeWorkScheduleOn($today);
            if ($workSchedule) {
                $breakHours = (float) ($workSchedule->break_hours ?? 0);
                // Legacy fallback: ca hành chính chưa set break_hours -> 1h
                if ($breakHours === 0.0 && str_contains(strtolower($workSchedule->name ?? ''), 'hành chính')) {
                    $breakHours = 1.0;
                }
            }
            $rawHours    = $checkInTime->diffInMinutes($now) / 60;
            $actualHours = max(0, round($rawHours - $breakHours, 2));
            $actualHours = min($actualHours, 8.0); // Cap 8h/ngày (OT tính riêng qua đơn tăng ca)

            $todaysAttendance->check_out_time = $now;
            $todaysAttendance->actual_hours   = $actualHours;

            if ($workSchedule) {
                $endTime = Carbon::parse($today->toDateString() . ' ' . $workSchedule->end_time);
                // Ca làm việc kéo qua đêm
                if ($endTime->lt(Carbon::parse($today->toDateString() . ' ' . $workSchedule->start_time))) {
                    $endTime->addDay();
                }
                // Về sớm hơn giờ kết thúc ca -> ghi chú lại
                if ($now->lt($endTime)) {
                    $earlyMinutes = (int) $now->diffInMinutes($endTime);
                    if ($earlyMinutes > 5) {
                        $todaysAttendance->notes = ($todaysAttendance->notes ? $todaysAttendance->notes . ' | ' : '') . "Về sớm {$earlyMinutes} phút";
                    }
                }
            }

            $todaysAttendance->save();
            $action = 'Check-out (Cập nhật)';
        } else {
            // CHƯA CÓ CHECK-IN -> lần quét đầu tiên trong ngày
            $workSchedule = $employee->activeWorkScheduleOn($today);
            $status = 'present';

            if ($workSchedule) {
                $startTime = Carbon::parse($today->toDateString() . ' ' . $workSchedule->start_time);
                if ($now->gt($startTime)) {
                    $status = 'late';
                    Log::info("FaceID Check-in: Employee {$employee->id} marked as LATE (" . (int) $startTime->diffInMinutes($now) . ' mins).');
                }
            } else {
                Log::warning("FaceID Check-in: Employee {$employee->id} has no schedule. Defaulted to 'present'.");
            }

            Attendance::create([
                'employee_id'      => $employee->id,
                'date'             => $today->toDateString(),
                'check_in_time'    => $now,
                'status'           => $status,
                'check_out_time'   => null,
                'work_schedule_id' => $workSchedule?->id, // Ca làm việc tại thời điểm chấm công
            ]);
            $action = 'Check-in';
        }

        Cache::put($recentKey, $now->format('H:i:s'), now()->addSeconds(config('hr.kiosk.repeat_cooldown_seconds', 120)));

        Log::info('Kiosk attendance recorded', [
            'employee_id' => $employee->id,
            'action'      => $action,
            'distance'    => round($distance, 3),
            'ip'          => $request->ip(),
        ]);

        return response()->json([
            'matched'  => true,
            'name'     => $employee->full_name,
            'distance' => round($distance, 3),
            'action'   => $action,
            'message'  => "Chào {$employee->full_name}! Ghi nhận {$action} lúc {$now->format('H:i:s')} thành công.",
        ]);
    }

    /**
     * Kiểm tra trạng thái đăng ký và trả về URL avatar.
     */
    public function getRegistrationStatus(Request $request)
    {
        $user = Auth::user();
        $hasFaceData = $user->faceDescriptors !== null;
        $avatarUrl = null;

        if ($user->employee && $user->employee->avatar) {
            $avatarUrl = Storage::url($user->employee->avatar);
        }

        return response()->json([
            'has_face_data' => $hasFaceData,
            'avatar_url'    => $avatarUrl,
        ]);
    }

    /**
     * Tìm nhân viên có khuôn mặt gần nhất (khoảng cách Euclid) với vector vừa quét.
     *
     * @param  float[]  $probe  vector 128 chiều
     * @return array{0: User, 1: float}|null  [user, khoảng cách] hoặc null nếu không ai đủ gần
     */
    private function findBestMatch(array $probe): ?array
    {
        $threshold = (float) config('hr.kiosk.face_match_threshold', 0.5);
        $probe = array_map('floatval', $probe);
        $bestRow = null;
        $bestDistance = INF;

        FaceDescriptor::query()->select(['id', 'user_id', 'descriptor'])->chunkById(200, function ($rows) use ($probe, &$bestRow, &$bestDistance) {
            foreach ($rows as $row) {
                $stored = json_decode($row->descriptor, true);
                if (!$this->isValidDescriptor($stored)) {
                    continue;
                }
                $distance = $this->euclideanDistance($probe, array_values($stored));
                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestRow = $row;
                }
            }
        });

        if (!$bestRow || $bestDistance > $threshold) {
            return null;
        }

        $user = User::with('employee')->find($bestRow->user_id);

        return $user ? [$user, $bestDistance] : null;
    }

    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0.0;
        foreach ($a as $i => $value) {
            $diff = $value - (float) $b[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }

    private function isValidDescriptor($vector): bool
    {
        if (!is_array($vector) || count($vector) !== self::DESCRIPTOR_LENGTH) {
            return false;
        }
        foreach ($vector as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }

        return true;
    }
}
