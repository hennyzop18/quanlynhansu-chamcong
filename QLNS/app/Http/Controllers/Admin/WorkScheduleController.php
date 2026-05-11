<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use App\Http\Requests\StoreWorkScheduleRequest;
use App\Http\Requests\UpdateWorkScheduleRequest;

class WorkScheduleController extends Controller
{
    public function index()
    {
        $schedules = WorkSchedule::latest()->paginate(15);
        return view('admin.work_schedules.index', compact('schedules'));
    }

    public function create()
    {
        return view('admin.work_schedules.create');
    }

    public function store(StoreWorkScheduleRequest $request)
    {
        WorkSchedule::create($request->validated());
        return redirect()->route('admin.work-schedules.index')->with('success', 'Thêm ca làm việc thành công!');
    }

    public function show(WorkSchedule $workSchedule)
    {
        return redirect()->route('admin.work-schedules.edit', $workSchedule);
    }

    public function edit(WorkSchedule $workSchedule)
    {
        return view('admin.work_schedules.edit', compact('workSchedule'));
    }

    public function update(UpdateWorkScheduleRequest $request, WorkSchedule $workSchedule)
    {
        $workSchedule->update($request->validated());
        return redirect()->route('admin.work-schedules.index')->with('success', 'Cập nhật ca làm việc thành công!');
    }

    public function destroy(WorkSchedule $workSchedule)
    {
        $isUsedInEmployees = \App\Models\Employee::where('work_schedule_id', $workSchedule->id)->exists();
        $isUsedInSchedules = \App\Models\EmployeeSchedule::where('work_schedule_id', $workSchedule->id)->exists();
        $isUsedInMonthly   = \App\Models\MonthlyRegistration::where('work_schedule_id', $workSchedule->id)->exists();
        $isUsedInAttendances = \App\Models\Attendance::where('work_schedule_id', $workSchedule->id)->exists();

        if ($isUsedInEmployees || $isUsedInSchedules || $isUsedInMonthly || $isUsedInAttendances) {
            return redirect()->route('admin.work-schedules.index')
                ->with('error', 'Cảnh báo: Ca làm việc này đang có nhân viên sử dụng, đã lên lịch hoặc đã có dữ liệu chấm công. Không thể xóa!');
        }

        $workSchedule->delete();
        return redirect()->route('admin.work-schedules.index')->with('success', 'Đã chuyển ca làm việc vào thùng rác.');
    }

    public function trash()
    {
        $schedules = WorkSchedule::onlyTrashed()->latest()->paginate(15);
        return view('admin.work_schedules.trash', compact('schedules'));
    }

    public function restore($id)
    {
        $schedule = WorkSchedule::withTrashed()->findOrFail($id);
        $schedule->restore();
        return redirect()->route('admin.work-schedules.trash')->with('success', 'Khôi phục ca làm việc thành công!');
    }
}