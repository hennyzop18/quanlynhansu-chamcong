<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\OvertimeRequest;
use Carbon\Carbon;

class JuneAttendanceSeeder extends Seeder
{
    public function run()
    {
        // Lấy 2 nhân viên đại diện
        $fixedEmp = Employee::where('salary_type', 'fixed')->first();
        $hourlyEmp = Employee::where('salary_type', 'hourly')->first();

        if (!$fixedEmp || !$hourlyEmp) {
            $this->command->error('Vui lòng chạy Seeder tạo nhân viên trước!');
            return;
        }

        $employees = [$fixedEmp, $hourlyEmp];
        $month = 6;
        $year = 2026;

        foreach ($employees as $emp) {
            $this->command->info("Đang tạo dữ liệu cho: {$emp->full_name} ({$emp->salary_type})");

            // 1. Tạo Chấm công cơ bản (20 ngày công thường)
            for ($d = 1; $d <= 20; $d++) {
                $date = Carbon::create($year, $month, $d);
                if ($date->isWeekend()) continue;

                Attendance::updateOrCreate(
                    ['employee_id' => $emp->id, 'date' => $date->toDateString()],
                    [
                        'check_in_time' => $date->copy()->setTime(8, 0),
                        'check_out_time' => $date->copy()->setTime(17, 0),
                        'status' => 'present',
                        'actual_hours' => 8,
                        'work_schedule_id' => $emp->work_schedule_id ?? 1
                    ]
                );
            }

            // 2. Tạo đơn OT - TRƯỜNG HỢP 1: Ngày thường (Thứ 2 - 01/06) -> 1.5x
            OvertimeRequest::create([
                'employee_id' => $emp->id,
                'date' => Carbon::create($year, $month, 1)->toDateString(),
                'start_time' => '18:00',
                'end_time' => '20:00',
                'hours' => 2,
                'reason' => 'Tăng ca ngày thường (Test 1.5x)',
                'status' => 'approved'
            ]);

            // 3. Tạo đơn OT - TRƯỜNG HỢP 2: Chủ Nhật (07/06) -> 2.0x
            OvertimeRequest::create([
                'employee_id' => $emp->id,
                'date' => Carbon::create($year, $month, 7)->toDateString(),
                'start_time' => '08:00',
                'end_time' => '12:00',
                'hours' => 4,
                'reason' => 'Tăng ca Chủ Nhật (Test 2.0x)',
                'status' => 'approved'
            ]);

            // 4. Tạo đơn OT - TRƯỜNG HỢP 3: Ngày lễ (02/09)
            OvertimeRequest::create([
                'employee_id' => $emp->id,
                'date' => Carbon::create(2026, 9, 2)->toDateString(),
                'start_time' => '08:00',
                'end_time' => '17:00',
                'hours' => 8,
                'reason' => 'Tăng ca Quốc Khánh (Test 3.0x)',
                'status' => 'approved'
            ]);
            
            // Tăng ca 01/05
            OvertimeRequest::create([
                'employee_id' => $emp->id,
                'date' => Carbon::create(2026, 5, 1)->toDateString(),
                'start_time' => '08:00',
                'end_time' => '17:00',
                'hours' => 8,
                'reason' => 'Tăng ca Quốc tế lao động (Test 3.0x)',
                'status' => 'approved'
            ]);
        }
    }
}
