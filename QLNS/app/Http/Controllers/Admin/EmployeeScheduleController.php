<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\WorkSchedule;
use App\Models\EmployeeSchedule; // Import model trung gian
use App\Http\Requests\StoreEmployeeScheduleRequest; // Import Form Request
use App\Http\Requests\UpdateEmployeeScheduleRequest; // Import Form Request
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmployeeScheduleController extends Controller
{
    /**
     * Hiển thị danh sách lịch làm việc đã gán cho một nhân viên cụ thể.
     */
    public function index(Employee $employee)
    {
        // Lấy các lịch đã gán, eager load tên lịch, sắp xếp theo ngày bắt đầu mới nhất
        $schedules = $employee->employeeSchedules()
            ->with('workSchedule:id,name') // Chỉ lấy id và name của workSchedule
            ->orderBy('start_date', 'desc')
            ->paginate(15); // Hoặc ->get() nếu không cần phân trang

        return view('admin.employees.schedules.index', compact('employee', 'schedules'));
    }

    /**
     * Hiển thị form để gán lịch làm việc mới cho nhân viên.
     */
    public function create(Employee $employee)
    {
        // Lấy danh sách tất cả WorkSchedules để chọn
        $workSchedules = WorkSchedule::orderBy('name')->get(['id', 'name']); // Chỉ lấy id và name

        return view('admin.employees.schedules.create', compact('employee', 'workSchedules'));
    }

    /**
     * Lưu lịch làm việc mới được gán cho nhân viên.
     */
    public function store(StoreEmployeeScheduleRequest $request, Employee $employee)
    {
        $validated = $request->validated();
        $newStartDate = Carbon::parse($validated['start_date']);
        $newEndDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

        DB::beginTransaction(); // Bắt đầu transaction
        try {
            // Xử lý Overlap/Kết thúc lịch cũ (như logic đã có)
            $currentOrFutureSchedules = $employee->employeeSchedules()
                ->where(function ($query) use ($newStartDate) {
                    $query->whereNull('end_date')
                        ->orWhere('end_date', '>=', $newStartDate->toDateString());
                })
                ->orderBy('start_date', 'desc')
                ->get();

            foreach ($currentOrFutureSchedules as $schedule) {
                if ($schedule->start_date->lt($newStartDate)) {
                    $newEndDateForOldSchedule = $newStartDate->copy()->subDay();
                    if ($schedule->end_date === null || $schedule->end_date->gt($newEndDateForOldSchedule)) {
                        $schedule->update(['end_date' => $newEndDateForOldSchedule]);
                    }
                }
            }

            // Tạo bản ghi gán lịch mới
            $employee->employeeSchedules()->create([
                'work_schedule_id' => $validated['work_schedule_id'],
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
            ]);

            // **** CẬP NHẬT employees.work_schedule_id ****
            $currentSchedule = $employee->getCurrentOrDefaultWorkSchedule();
            $employee->update(['work_schedule_id' => $currentSchedule?->id]); // Cập nhật ID lịch hiện tại vào bảng employee
            // **** KẾT THÚC CẬP NHẬT ****

            DB::commit(); // Commit transaction

            return redirect()->route('admin.employees.schedules.index', $employee)
                ->with('success', 'Gán lịch làm việc mới thành công!');

        } catch (\Exception $e) {
            DB::rollBack(); // Rollback nếu có lỗi
            \Log::error("Error storing employee schedule for employee {$employee->id}: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Đã xảy ra lỗi khi gán lịch.');
        }
    }

    /**
     * Hiển thị form để sửa một lịch làm việc đã gán.
     */
    public function edit(Employee $employee, EmployeeSchedule $schedule_assignment) // Binding model EmployeeSchedule
    {
        $workSchedules = WorkSchedule::orderBy('name')->get(['id', 'name']);

        // Đảm bảo bản ghi gán lịch này thuộc về đúng nhân viên (thường không cần nếu route đúng)
        // if ($schedule_assignment->employee_id !== $employee->id) {
        //     abort(404);
        // }

        return view('admin.employees.schedules.edit', compact('employee', 'schedule_assignment', 'workSchedules'));
    }

    /**
     * Cập nhật một lịch làm việc đã gán.
     */
    public function update(UpdateEmployeeScheduleRequest $request, Employee $employee, EmployeeSchedule $schedule_assignment)
    {
        $validated = $request->validated();
        $newStartDate = Carbon::parse($validated['start_date']);
        $newEndDate = $validated['end_date'] ? Carbon::parse($validated['end_date']) : null;

        DB::beginTransaction();
        try {
            // TODO: Xử lý overlap phức tạp hơn khi update nếu cần

            // Cập nhật bản ghi gán lịch
            $schedule_assignment->update([
                'work_schedule_id' => $validated['work_schedule_id'],
                'start_date' => $newStartDate,
                'end_date' => $newEndDate,
            ]);

            // **** CẬP NHẬT employees.work_schedule_id ****
            $currentSchedule = $employee->getCurrentOrDefaultWorkSchedule();
            $employee->update(['work_schedule_id' => $currentSchedule?->id]);
            // **** KẾT THÚC CẬP NHẬT ****

            DB::commit();

            return redirect()->route('admin.employees.schedules.index', $employee)
                ->with('success', 'Cập nhật gán lịch làm việc thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error updating employee schedule ID {$schedule_assignment->id}: " . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Đã xảy ra lỗi khi cập nhật lịch.');
        }
    }

    /**
     * Xóa một lịch làm việc đã gán.
     */
    public function destroy(Employee $employee, EmployeeSchedule $schedule_assignment)
    {
        DB::beginTransaction();
        try {
            $schedule_assignment->delete(); // Xóa bản ghi gán lịch

            // **** CẬP NHẬT employees.work_schedule_id ****
            $currentSchedule = $employee->getCurrentOrDefaultWorkSchedule(); // Tìm lại lịch hiện tại sau khi xóa
            $employee->update(['work_schedule_id' => $currentSchedule?->id]);
            // **** KẾT THÚC CẬP NHẬT ****

            DB::commit();

            return redirect()->route('admin.employees.schedules.index', $employee)
                ->with('success', 'Xóa gán lịch làm việc thành công!');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error deleting employee schedule ID {$schedule_assignment->id}: " . $e->getMessage());
            return redirect()->route('admin.employees.schedules.index', $employee)
                ->with('error', 'Đã xảy ra lỗi khi xóa lịch.');
        }
    }
}