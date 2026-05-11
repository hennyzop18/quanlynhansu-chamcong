<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class HourlyEmployeeSeeder extends Seeder
{
    public function run()
    {
        // 1. Tạo nhân viên John Doe tính lương theo giờ
        $employee = Employee::updateOrCreate(
            ['employee_code' => 'NV-HOURLY-01'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'gender' => 'male',
                'dob' => '1995-01-01',
                'phone_number' => '0987654321',
                'personal_email' => 'johndoe@example.com',
                'hire_date' => '2026-01-01',
                'status' => 'active',
                'salary_type' => 'hourly',
                'hourly_rate' => 100000, // 100k/giờ
                'base_salary' => 0,
                'insurance_salary' => 5000000,
                'position_id' => 1,
                'department_id' => 1,
            ]
        );

        // 2. Tạo dữ liệu chấm công cho tháng 4/2026 (20 ngày làm việc)
        $start = Carbon::create(2026, 4, 1);
        $end = Carbon::create(2026, 4, 30);
        $period = CarbonPeriod::create($start, $end);

        $workDaysCount = 0;
        foreach ($period as $date) {
            if ($date->dayOfWeek !== 0 && $date->dayOfWeek !== 6 && $workDaysCount < 20) {
                Attendance::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'date' => $date->toDateString(),
                    ],
                    [
                        'check_in_time' => $date->copy()->setTime(8, 0, 0),
                        'check_out_time' => $date->copy()->setTime(17, 0, 0),
                        'status' => 'present',
                        'actual_hours' => 8.0,
                        'notes' => 'Chấm công theo giờ mẫu'
                    ]
                );
                $workDaysCount++;
            }
        }

        // 3. Tạo 10 giờ tăng ca (OT) đã được duyệt (ví dụ từ 17:00 đến 22:00 trong 2 ngày)
        OvertimeRequest::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => '2026-04-15',
                'start_time' => '17:00:00',
            ],
            [
                'end_time' => '22:00:00',
                'hours' => 5.0,
                'reason' => 'Dữ liệu OT mẫu 1',
                'status' => 'approved'
            ]
        );

        OvertimeRequest::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'date' => '2026-04-16',
                'start_time' => '17:00:00',
            ],
            [
                'end_time' => '22:00:00',
                'hours' => 5.0,
                'reason' => 'Dữ liệu OT mẫu 2',
                'status' => 'approved'
            ]
        );
    }
}
