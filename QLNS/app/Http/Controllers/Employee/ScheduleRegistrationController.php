<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WorkSchedule;
use App\Models\MonthlyRegistration;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class ScheduleRegistrationController extends Controller
{
    const TARGET_HOURS = 176;
    const MINIMUM_HOURS = 150;

    public function index(Request $request)
    {
        $employee = Auth::user()->employee;
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        // === THAY ĐỔI LOGIC LẤY THÁNG/NĂM TẠI ĐÂY ===
        $year = $request->input('year_select', Carbon::now()->year);
        $month = $request->input('month_select', Carbon::now()->month);
        // Ghép lại để tạo đối tượng Carbon
        $targetDate = Carbon::createFromDate($year, $month, 1);
        // ===========================================
        
        // Lấy tất cả ca làm việc có sẵn
        $workSchedules = WorkSchedule::all();
        
        // Lấy lịch đã đăng ký của nhân viên trong tháng
        $registrations = $employee->monthlyRegistrations()
            ->whereYear('date', $targetDate->year)
            ->whereMonth('date', $targetDate->month)
            ->get()
            ->keyBy(fn($reg) => $reg->date->format('Y-m-d')); // Key by date để dễ truy cập trong view
            
        $totalRegisteredHours = $registrations->sum('scheduled_hours');

        return view('employee.schedule.index', [
            'targetDate' => $targetDate,
            'workSchedules' => $workSchedules,
            'registrations' => $registrations,
            'totalRegisteredHours' => $totalRegisteredHours,
            'targetHours' => self::TARGET_HOURS,
            'minimumHours' => self::MINIMUM_HOURS,
        ]);
    }

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;
        $validated = $request->validate([
            'month' => 'required|date_format:Y-m',
            'schedules' => 'required|array',
            'schedules.*' => ['nullable', Rule::exists('work_schedules', 'id')],
        ]);

        $targetDate = Carbon::parse($validated['month'] . '-01');
        $schedules = $validated['schedules'];
        $totalHours = 0;

        $workSchedules = WorkSchedule::findMany(array_filter(array_values($schedules)))
            ->keyBy('id');

        foreach ($schedules as $dateString => $scheduleId) {
            $date = Carbon::parse($dateString);

            // Chỉ cho phép đăng ký trong tháng đã chọn và từ ngày mai trở đi
            if (!$date->isSameMonth($targetDate) || $date->isPast()) {
                continue;
            }

            // Xóa đăng ký cũ nếu người dùng chọn "Trống"
            if (empty($scheduleId)) {
                $employee->monthlyRegistrations()->where('date', $date)->delete();
                continue;
            }

            $schedule = $workSchedules->get($scheduleId);
            if (!$schedule) continue;

            // FLAW#6 fix: Kiểm tra nếu ngày này đã có lịch APPROVED → không cho ghi đè
            $existing = MonthlyRegistration::where('employee_id', $employee->id)
                ->where('date', $date->toDateString())
                ->first();
            
            if ($existing && $existing->status === 'approved') {
                // Bỏ qua ngày đã được duyệt, không ghi đè
                continue;
            }
            
            $hours = $schedule->duration_in_hours;
            $totalHours += $hours;

            MonthlyRegistration::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $date,
                ],
                [
                    'work_schedule_id' => $scheduleId,
                    'scheduled_hours' => $hours,
                    'status' => 'pending', // Mặc định là chờ duyệt
                ]
            );
        }
        
        // Kiểm tra tổng số giờ sau khi đã lưu
        $finalTotalHours = $employee->monthlyRegistrations()
            ->whereYear('date', $targetDate->year)
            ->whereMonth('date', $targetDate->month)
            ->sum('scheduled_hours');

        if ($finalTotalHours < self::MINIMUM_HOURS) {
            return redirect()->back()->with('warning', 'Đăng ký thành công, nhưng tổng số giờ của bạn chưa đạt mức tối thiểu.');
        }

        return redirect()->route('employee.schedule.index', ['month' => $validated['month']])
            ->with('success', 'Đã lưu lịch đăng ký thành công!');
    }
}