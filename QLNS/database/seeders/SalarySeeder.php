<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Salary;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Import DB

class SalarySeeder extends Seeder
{
    public function run(): void
    {
        // Xóa dữ liệu cũ nếu muốn seeder chạy lại được nhiều lần
        // Salary::query()->delete(); // Hoặc truncate nếu không có khóa ngoại phức tạp

        $employees = Employee::where('status', 'active')->get();
        $numberOfMonths = 6; // Tạo dữ liệu lương cho 6 tháng gần nhất

        foreach ($employees as $employee) {
            for ($i = 0; $i < $numberOfMonths; $i++) {
                $date = Carbon::now()->subMonths($i);
                $payPeriodStart = $date->copy()->startOfMonth();
                $payPeriodEnd = $date->copy()->endOfMonth();

                // --- Logic Tính toán lương mẫu (tương tự trong SalaryController@store) ---
                $baseSalary = $employee->base_salary ?? rand(7000000, 25000000);
                $taxableAllowances = rand(0, 1) ? rand(500000, 2000000) : 0;
                $nontaxableAllowances = rand(0, 1) ? rand(200000, 1000000) : 0;
                $deductions = rand(0, 1) ? rand(100000, 1500000) : 0;
                $bonus = rand(0, 1) < 0.3 ? rand(200000, 5000000) : 0;
                $fines = rand(0, 1) < 0.1 ? rand(50000, 500000) : 0;
                $netSalary = max(0, $baseSalary + $taxableAllowances + $nontaxableAllowances - $deductions + $bonus - $fines);
                $isPaid = rand(0, 1) < 0.8;

                // Sử dụng updateOrCreate để tránh lỗi trùng lặp khóa unique
                Salary::updateOrCreate(
                    [
                        'employee_id' => $employee->id,
                        'pay_period_start' => $payPeriodStart->toDateString(), // Chỉ lưu ngày
                        'pay_period_end' => $payPeriodEnd->toDateString(),   // Chỉ lưu ngày
                    ],
                    [
                        'base_salary' => $baseSalary,
                        'standard_work_days' => 22,
                        'actual_work_days' => 22,
                        'taxable_allowances' => $taxableAllowances,
                        'nontaxable_allowances' => $nontaxableAllowances,
                        'deductions' => $deductions,
                        'bonus' => $bonus,
                        'fines' => $fines,
                        'net_salary' => $netSalary,
                        'status' => $isPaid ? 'paid' : 'pending',
                        'paid_date' => $isPaid ? $payPeriodEnd->copy()->addDays(rand(5, 10))->toDateString() : null,
                        'notes' => rand(0, 1) < 0.1 ? 'Sample seed data note' : null,
                    ]
                );
            }
        }
    }
}