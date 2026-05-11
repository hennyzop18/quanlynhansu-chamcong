<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee; // Có thể cần để lọc
use App\Models\Department; // Có thể cần để lọc
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the attendance records.
     */
    public function index(Request $request)
    {
        // Lấy tham số lọc từ request (ví dụ: date, employee_id, department_id)
        $filterDate = $request->input('date', Carbon::today()->toDateString()); // Mặc định là hôm nay
        $filterEmployee = $request->input('employee_id');
        $filterDepartment = $request->input('department_id'); // Cần query phức tạp hơn

        $query = Attendance::with([
            'employee' => function ($q) {
                $q->select('id', 'first_name', 'last_name', 'employee_code', 'department_id'); // Chỉ lấy cột cần thiết
            },
            'employee.department:id,name',
            'admin:id,name'
        ])
            ->whereDate('date', $filterDate)
            ->orderBy('check_in_time', 'asc');

        if ($filterEmployee) {
            $query->where('employee_id', $filterEmployee);
        }

        // Lọc theo phòng ban (nếu được chọn)
        if ($filterDepartment) {
            $query->whereHas('employee', function ($q) use ($filterDepartment) {
                $q->where('department_id', $filterDepartment);
            });
        }

        $attendances = $query->paginate(25)->withQueryString(); // Giữ lại query params khi phân trang

        $employees = Employee::where('status', 'active')->orderBy('first_name')->select('id', 'first_name', 'last_name')->get(); // Lấy danh sách NV active để lọc

        $departments = Department::orderBy('name')->select('id', 'name')->get(); // Lấy phòng ban để lọc

        return view('admin.attendances.index', compact('attendances', 'employees', 'departments', 'filterDate', 'filterEmployee', 'filterDepartment'));
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Attendance $attendance)
    {
        // Load employee để hiển thị thông tin
        $attendance->load('employee:id,first_name,last_name,employee_code');
        return view('admin.attendances.edit', compact('attendance'));
    }

    /**
     * Update the specified attendance record in storage.
     */
    public function update(Request $request, Attendance $attendance)
    {

        $validated = $request->validate([
            'check_in_time_time' => 'nullable|date_format:H:i:s',
            'check_out_time_time' => [
                'nullable',
                'date_format:H:i:s',
                function ($attribute, $value, $fail) use ($request, $attendance) {
                    $checkInTimeStr = $request->input('check_in_time_time') ?? $attendance->check_in_time?->format('H:i:s');
                    if ($value && $checkInTimeStr && $value < $checkInTimeStr) {
                        $fail('Giờ Check-out không được trước giờ Check-in.');
                    }
                },
            ],
            'notes' => 'nullable|string|max:500',
        ]);

        $employee = $attendance->employee;
        $attendanceDate = $attendance->date;
        $originalDateStr = $attendanceDate->format('Y-m-d');

        $updateData = [];
        $finalCheckInTime = $attendance->check_in_time;
        $finalCheckOutTime = $attendance->check_out_time;
        $changed = false; // Cờ kiểm tra thay đổi

        // --- Xử lý giờ check-in và xác định finalCheckInTime ---
        if ($request->has('check_in_time_time')) {
            if ($request->filled('check_in_time_time')) {
                $newTime = Carbon::parse($originalDateStr . ' ' . $validated['check_in_time_time']);
                if (!$finalCheckInTime || $finalCheckInTime->ne($newTime)) {
                    $updateData['check_in_time'] = $newTime;
                    $finalCheckInTime = $newTime;
                    $changed = true;
                }
            } else {
                if ($finalCheckInTime !== null) {
                    $updateData['check_in_time'] = null;
                    $finalCheckInTime = null;
                    $changed = true;
                }
            }
        }
        // --- Xử lý giờ check-out ---
        if ($request->has('check_out_time_time')) {
            if ($request->filled('check_out_time_time')) {
                $newTime = Carbon::parse($originalDateStr . ' ' . $validated['check_out_time_time']);
                if (!$finalCheckOutTime || $finalCheckOutTime->ne($newTime)) {
                    $updateData['check_out_time'] = $newTime;
                    $finalCheckOutTime = $newTime; // FLAW#5 fix: cần cập nhật để tính status đúng
                    $changed = true;
                }
            } else {
                if ($finalCheckOutTime !== null) {
                    $updateData['check_out_time'] = null;
                    $finalCheckOutTime = null; // FLAW#5 fix: khi xóa checkout phải cập nhật biến
                    $changed = true;
                }
            }
        }
        // --- Xử lý notes ---
        if ($request->has('notes')) {
            $newNotes = $validated['notes'];
            if ($newNotes !== $attendance->notes) {
                $adminNote = "\nAdmin updated at " . now()->toDateTimeString() . ".";
                $updateData['notes'] = ($newNotes ?? '') . $adminNote;
                $changed = true;
            }
        }

        // **** TÍNH TOÁN LẠI TRẠNG THÁI (STATUS) THEO LOGIC MỚI ****
        $calculatedStatus = null; // Status mới sẽ được tính toán
        $statusChanged = false;

        // 1. Trường hợp VẮNG MẶT: Nếu cả check-in và check-out cuối cùng đều là null
        if ($finalCheckInTime === null && $finalCheckOutTime === null) {
            $calculatedStatus = 'absent';
            Log::info("Attendance ID {$attendance->id}: Marked as ABSENT (no check-in or check-out).");
        }
        // 2. Trường hợp CÓ CHECK-IN: Tính toán present/late
        elseif ($finalCheckInTime) {
            $workSchedule = $employee->activeWorkScheduleOn($attendanceDate);
            Log::info("Attendance ID {$attendance->id}: Calculating present/late. Schedule found:", ['schedule' => $workSchedule ? $workSchedule->id : null]);

            if ($workSchedule) {
                // Kiểm tra ĐI TRỄ dựa vào isLate() (so sánh với late_threshold hoặc start_time)
                if ($workSchedule->isLate($finalCheckInTime)) {
                    $calculatedStatus = 'late';
                } else {
                    // Nếu không trễ -> ĐÚNG GIỜ
                    $calculatedStatus = 'present';
                }
            } else {
                // Không có lịch -> Không xác định được trễ/đúng giờ -> Để NULL
                $calculatedStatus = null;
                Log::warning("Attendance ID {$attendance->id}: Cannot determine status on {$attendanceDate->toDateString()} due to missing schedule.");
            }
        }
        // 3. Trường hợp chỉ có Check-out mà không có Check-in (ít xảy ra khi sửa): Trạng thái không xác định
        else {
            $calculatedStatus = null;
            Log::info("Attendance ID {$attendance->id}: Only CheckOut time found. Status set to NULL.");
        }

        // Chỉ thêm 'status' vào mảng update nếu giá trị tính toán khác giá trị hiện tại
        if ($calculatedStatus !== $attendance->status) {
            $updateData['status'] = $calculatedStatus;
            $statusChanged = true;
            $changed = true;
            Log::info("Status for Attendance ID {$attendance->id} will be updated. New calculated status:", ['status' => $calculatedStatus]);
        } else {
            Log::info("Calculated status (" . ($calculatedStatus !== null ? $calculatedStatus : 'NULL') . ") is same as current (" . ($attendance->status !== null ? $attendance->status : 'NULL') . "). Status update skipped.");
        }
        // **** KẾT THÚC TÍNH TOÁN STATUS ****


        // ─── C2: Tính lại actual_hours khi admin sửa giờ ─────────────
        if ($changed && $finalCheckInTime && $finalCheckOutTime) {
            $workScheduleForHours = $employee->activeWorkScheduleOn($attendanceDate);
            $breakHours = 0.0;
            if ($workScheduleForHours) {
                $breakHours = (float) ($workScheduleForHours->break_hours ?? 0);
                if ($breakHours === 0.0 && str_contains(strtolower($workScheduleForHours->name ?? ''), 'hành chính')) {
                    $breakHours = 1.0;
                }
            }
            $rawHours    = $finalCheckInTime->diffInMinutes($finalCheckOutTime) / 60;
            $actualHours = max(0, round($rawHours - $breakHours, 2));
            $actualHours = min($actualHours, 8.0); // Cap 8h/ngày
            $updateData['actual_hours'] = $actualHours;
            Log::info("Recalculated actual_hours for Attendance ID {$attendance->id}: {$actualHours}h");
        } elseif ($changed && (!$finalCheckInTime || !$finalCheckOutTime)) {
            // Nếu xóa một trong hai giờ → actual_hours = null
            $updateData['actual_hours'] = null;
        }
        // ─────────────────────────────────────────────────────────────

        // Chỉ gọi update nếu thực sự có dữ liệu thay đổi
        if ($changed) {
            $updateData['admin_id'] = auth()->id(); // Ghi vết Admin thực hiện thay đổi
            Log::info("Updating Attendance ID {$attendance->id} with data:", $updateData);
            $attendance->update($updateData);
        } else {
            Log::info("No effective changes for Attendance ID {$attendance->id}. Skipping update.");
        }

        // Redirect về trang index với bộ lọc cũ
        $queryParams = ['date' => $originalDateStr];
        if (request('employee_id'))
            $queryParams['employee_id'] = request('employee_id');
        if (request('department_id'))
            $queryParams['department_id'] = request('department_id');

        return redirect()->route('admin.attendances.index', $queryParams)->with('success', 'Cập nhật chấm công thành công!');
    }

    // Có thể thêm phương thức `store` nếu muốn Admin tạo chấm công thủ công
    // public function store(Request $request) { ... }
}