<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // Sử dụng Carbon để xử lý ngày giờ

class EmployeeAttendanceController extends Controller
{
    // // Hiển thị trang chấm công (nút check-in/out)
    // public function create()
    // {
    //     $employeeId = Auth::user()->employee_id;
    //     $today = Carbon::today();

    //     // Kiểm tra xem hôm nay đã chấm công chưa
    //     $todaysAttendance = Attendance::where('employee_id', $employeeId)
    //         ->where('date', $today)
    //         ->first();

    //     $checkedIn = $todaysAttendance && $todaysAttendance->check_in_time;
    //     $checkedOut = $todaysAttendance && $todaysAttendance->check_out_time;

    //     return view('employee.attendance.create', compact('checkedIn', 'checkedOut', 'todaysAttendance'));
    // }

    // Xử lý Check-in
    // public function storeCheckIn(Request $request)
    // {
    //     $user = Auth::user();
    //     $employee = $user->employee;

    //     if (!$employee) {
    //         return redirect()->route('employee.attendance.create')->with('error', 'Nhân viên không tồn tại.');
    //     }

    //     $now = Carbon::now();
    //     $today = $now->copy()->startOfDay(); // Lấy ngày hôm nay, bỏ phần giờ phút giây

    //     // Kiểm tra check-in tồn tại (như cũ)
    //     $todaysAttendance = Attendance::where('employee_id', $employee->id)
    //         ->where('date', $today->toDateString())
    //         ->first();
    //     if ($todaysAttendance) {
    //         if ($todaysAttendance->check_in_time) {
    //             return redirect()->route('employee.attendance.create')->with('error', 'Bạn đã Check-in hôm nay rồi.');
    //         }
    //     }
    //     // ...

    //     // **** XÁC ĐỊNH TRẠNG THÁI CHECK-IN MỚI ****
    //     $workSchedule = $employee->activeWorkScheduleOn($today);
    //     $status = null; // Bắt đầu bằng null

    //     if ($workSchedule) { // Chỉ tính status nếu có lịch
    //         // 1. Kiểm tra xem có phải vắng mặt không (check-in sau giờ kết thúc)
    //         if ($workSchedule->isAbsentByCheckInTime($now)) {
    //             $status = 'absent';
    //             Log::info("Employee {$employee->id} check-in marked as ABSENT.", ['check_in' => $now->format('H:i:s'), 'schedule_end' => $workSchedule->end_time]);
    //         }
    //         // 2. Nếu không vắng, kiểm tra xem có đi trễ không
    //         elseif ($workSchedule->isLate($now)) {
    //             $status = 'late';
    //             Log::info("Employee {$employee->id} check-in marked as LATE.", ['check_in' => $now->format('H:i:s'), 'late_threshold' => $workSchedule->late_threshold ?? $workSchedule->start_time]);
    //         }
    //         // 3. Nếu không vắng và không trễ -> đúng giờ
    //         else {
    //             $status = 'present';
    //             Log::info("Employee {$employee->id} check-in marked as PRESENT.");
    //         }
    //     } else {
    //         // Không có lịch -> status là null (hoặc 'present' tùy chính sách)
    //         $status = null; // Hoặc 'present'
    //         Log::warning("Employee {$employee->id} checked in on {$today->toDateString()} without schedule. Status set to NULL.");
    //     }
    //     // **** KẾT THÚC XÁC ĐỊNH TRẠNG THÁI ****

    //     Log::info("Employee {$employee->id} check-in status:", ['status' => $status]);

    //     Attendance::updateOrCreate(
    //         ['employee_id' => $employee->id, 'date' => $today->toDateString()],
    //         ['check_in_time' => $now, 'status' => $status] // Gán status đã tính
    //     );
    //     return redirect()->route('employee.attendance.create')->with('success', 'Check-in thành công!');
    // }

    // // Xử lý Check-out
    // public function storeCheckOut(Request $request)
    // {
    //     $employeeId = Auth::user()->employee_id;
    //     $now = Carbon::now();
    //     $today = $now->copy()->toDateString();

    //     $attendance = Attendance::where('employee_id', $employeeId)
    //         ->where('date', $today)
    //         ->first();

    //     // Kiểm tra xem đã check-in chưa và chưa check-out
    //     if (!$attendance || !$attendance->check_in_time) {
    //         return redirect()->route('employee.attendance.create')->with('error', 'Bạn chưa Check-in hôm nay.');
    //     }

    //     if ($attendance->check_out_time) {
    //         return redirect()->route('employee.attendance.create')->with('error', 'Bạn đã Check-out hôm nay rồi.');
    //     }

    //     // Kiểm tra check-out phải sau check-in
    //     if ($now->lt($attendance->check_in_time)) {
    //         return redirect()->route('employee.attendance.create')->with('error', 'Giờ Check-out không hợp lệ (trước giờ Check-in).');
    //     }

    //     $attendance->update(['check_out_time' => $now]);
    //     // Có thể tính toán tổng giờ làm việc ở đây nếu cần

    //     return redirect()->route('employee.attendance.create')->with('success', 'Check-out thành công!');
    // }

    // Xem lịch sử chấm công cá nhân
    public function history(Request $request)
    {
        $employeeId = Auth::user()->employee_id;
        $attendances = Attendance::where('employee_id', $employeeId)
            ->orderBy('date', 'desc')
            ->paginate(30); // Phân trang lịch sử

        return view('employee.attendance.history', compact('attendances'));
    }
}