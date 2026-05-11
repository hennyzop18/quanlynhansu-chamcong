<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class DummyAttendanceSeeder extends Seeder
{
    public function run()
    {
        $month = 4;
        $year = 2026;
        $start = Carbon::create($year, $month, 1);
        $end = Carbon::create($year, $month, 30);
        $period = CarbonPeriod::create($start, $end);

        // 1. Xóa dữ liệu lương cũ của tháng 4 để tính lại
        \App\Models\Salary::where('pay_period_start', $start->toDateString())
            ->where('pay_period_end', $end->toDateString())
            ->delete();
        
        // 2. Xóa dữ liệu chấm công cũ của tháng 4 để tạo mới cho chuẩn
        Attendance::whereBetween('date', [$start->toDateString(), $end->toDateString()])->delete();

        $employees = Employee::all();
        foreach ($employees as $employee) {
            foreach ($period as $date) {
                // Bỏ qua Chủ Nhật
                if ($date->dayOfWeek === 0) continue;

                Attendance::create([
                    'employee_id' => $employee->id,
                    'date' => $date->toDateString(),
                    'check_in_time' => $date->copy()->setTime(8, 0, 0),
                    'check_out_time' => $date->copy()->setTime(17, 0, 0),
                    'status' => 'present',
                    'actual_hours' => 8.0,
                    'notes' => 'Dữ liệu chuẩn tháng 4'
                ]);
            }
        }
        
        echo "Đã làm sạch và tạo mới dữ liệu cho " . $employees->count() . " nhân viên trong Tháng 4/2026.\n";
    }
}
