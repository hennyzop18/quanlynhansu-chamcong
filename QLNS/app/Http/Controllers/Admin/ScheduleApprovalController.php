<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\WorkSchedule;
use App\Models\MonthlyRegistration;
use Carbon\Carbon;

class ScheduleApprovalController extends Controller
{
    public function index(Request $request)
    {
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();
        
        $selectedEmployeeId = $request->input('employee_id');
        
        // === THAY ĐỔI LOGIC LẤY THÁNG/NĂM TẠI ĐÂY ===
        $year = $request->input('year_select', Carbon::now()->year);
        $month = $request->input('month_select', Carbon::now()->month);
        // Ghép lại để tạo đối tượng Carbon
        $targetDate = Carbon::createFromDate($year, $month, 1);
        // ===========================================
        
        $registrations = collect();
        $totalRegisteredHours = 0;
        $selectedEmployee = null;

        if ($selectedEmployeeId) {
            $selectedEmployee = Employee::find($selectedEmployeeId);
            $registrations = $selectedEmployee->monthlyRegistrations()
                ->whereYear('date', $targetDate->year)
                ->whereMonth('date', $targetDate->month)
                ->get()
                ->keyBy(fn($reg) => $reg->date->format('Y-m-d'));
            
            $totalRegisteredHours = $registrations->sum('scheduled_hours');
        }
        
        $workSchedules = WorkSchedule::all();

        return view('admin.schedule_approvals.index', [
            'employees' => $employees,
            'selectedEmployee' => $selectedEmployee,
            'targetDate' => $targetDate,
            'workSchedules' => $workSchedules,
            'registrations' => $registrations,
            'totalRegisteredHours' => $totalRegisteredHours,
        ]);
    }

    public function approve(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|date_format:Y-m',
            'schedules' => 'required|array',
            'schedules.*.work_schedule_id' => 'nullable|exists:work_schedules,id',
            'schedules.*.status' => 'required|in:pending,approved,rejected',
        ]);

        $employee = Employee::find($validated['employee_id']);
        $targetDate = Carbon::parse($validated['month'] . '-01');
        
        $workSchedules = WorkSchedule::findMany(
            collect($validated['schedules'])->pluck('work_schedule_id')->filter()
        )->keyBy('id');

        foreach ($validated['schedules'] as $dateString => $data) {
            $date = Carbon::parse($dateString);
            
            // Chỉ xử lý các ngày trong tháng được chọn
            if (!$date->isSameMonth($targetDate)) continue;

            $scheduleId = $data['work_schedule_id'];
            $status = $data['status'];

            // Xóa đăng ký nếu admin chọn "Trống"
            if (empty($scheduleId)) {
                $employee->monthlyRegistrations()->where('date', $date)->delete();
                continue;
            }

            $schedule = $workSchedules->get($scheduleId);
            if (!$schedule) continue;

            MonthlyRegistration::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $date,
                ],
                [
                    'work_schedule_id' => $scheduleId,
                    'scheduled_hours' => $schedule->duration_in_hours,
                    'status' => $status,
                ]
            );
        }

        return redirect()->route('admin.schedule_approvals.index', [
            'employee_id' => $employee->id,
            'month' => $validated['month']
        ])->with('success', 'Đã cập nhật và phê duyệt lịch thành công!');
    }
}