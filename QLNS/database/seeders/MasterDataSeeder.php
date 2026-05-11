<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Salary;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        $months = [4, 5, 6];
        $year = 2026;

        $this->command->info("--- ĐANG DỌN DẸP DỮ LIỆU CŨ ---");
        foreach ($months as $m) {
            $start = Carbon::create($year, $m, 1)->startOfMonth()->toDateString();
            $end = Carbon::create($year, $m, 1)->endOfMonth()->toDateString();

            Attendance::whereBetween('date', [$start, $end])->delete();
            Salary::whereBetween('pay_period_start', [$start, $end])->delete();
            OvertimeRequest::whereBetween('date', [$start, $end])->delete();
        }

        $this->command->info("--- ĐANG TẠO DỮ LIỆU MỚI THÁNG 4, 5, 6 ---");

        $fixedEmp = Employee::where('salary_type', 'fixed')->first();
        $hourlyEmp = Employee::where('salary_type', 'hourly')->first();

        if (!$fixedEmp || !$hourlyEmp) {
            $this->command->error("Không tìm thấy nhân viên mẫu. Vui lòng chạy EmployeeSeeder trước.");
            return;
        }

        foreach ([$fixedEmp, $hourlyEmp] as $emp) {
            foreach ($months as $m) {
                $startDate = Carbon::create($year, $m, 1);
                $endDate = $startDate->copy()->endOfMonth();

                // 1. Tạo chấm công (Tất cả ngày thường trong tháng)
                for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
                    if ($date->isWeekend()) continue;

                    Attendance::create([
                        'employee_id' => $emp->id,
                        'date' => $date->toDateString(),
                        'check_in_time' => $date->copy()->setTime(8, 0),
                        'check_out_time' => $date->copy()->setTime(17, 0),
                        'status' => 'present',
                        'actual_hours' => 8,
                        'work_schedule_id' => $emp->work_schedule_id ?? 1
                    ]);
                }

                // 2. Tạo các case OT đặc biệt để test
                if ($m == 4) {
                    // Tháng 4: OT ngày thường 2h
                    $this->createOt($emp->id, Carbon::create($year, 4, 6), 2, 'OT Ngày thường T4');
                } 
                elseif ($m == 5) {
                    // Tháng 5: OT Ngày lễ 01/05 (8h) -> Test 3.0x
                    $this->createOt($emp->id, Carbon::create($year, 5, 1), 8, 'OT Ngày Quốc tế lao động');
                } 
                elseif ($m == 6) {
                    // Tháng 6: OT Chủ Nhật 07/06 (4h) -> Test 2.0x
                    $this->createOt($emp->id, Carbon::create($year, 6, 7), 4, 'OT Chủ Nhật T6');
                    // OT Ngày thường 10/06 (2h) -> Test 1.5x
                    $this->createOt($emp->id, Carbon::create($year, 6, 10), 2, 'OT Ngày thường T6');
                }
            }
        }

        $this->command->info("--- HOÀN THÀNH ĐỔ DỮ LIỆU ---");
    }

    private function createOt($empId, $date, $hours, $reason)
    {
        OvertimeRequest::create([
            'employee_id' => $empId,
            'date' => $date->toDateString(),
            'start_time' => '18:00',
            'end_time' => Carbon::parse('18:00')->addHours($hours)->format('H:i'),
            'hours' => $hours,
            'reason' => $reason,
            'status' => 'approved'
        ]);
    }
}
