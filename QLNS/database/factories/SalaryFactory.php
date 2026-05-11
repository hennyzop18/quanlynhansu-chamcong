<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;
use Carbon\Carbon;

class SalaryFactory extends Factory
{
    public function definition(): array
    {
        $employee = Employee::where('status', 'active')->inRandomOrder()->first() ?? Employee::factory()->create();
        // Giả sử employee có cột base_salary
        $baseSalary = $employee->base_salary ?? fake()->numberBetween(7000000, 25000000);
        $allowances = fake()->optional(0.7)->numberBetween(500000, 3000000) ?? 0; // 70% có phụ cấp
        $deductions = fake()->optional(0.5)->numberBetween(100000, 1500000) ?? 0; // 50% có khấu trừ
        $bonus = fake()->optional(0.3)->numberBetween(200000, 5000000) ?? 0;
        $fines = fake()->optional(0.1)->numberBetween(50000, 500000) ?? 0;
        $netSalary = max(0, $baseSalary + $allowances - $deductions + $bonus - $fines);

        // Tạo kỳ lương ngẫu nhiên trong 6 tháng qua
        $month = fake()->numberBetween(1, 12);
        $year = now()->subMonths(fake()->numberBetween(0, 6))->year;
        $payPeriodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $payPeriodEnd = $payPeriodStart->copy()->endOfMonth();

        $isPaid = fake()->boolean(80); // 80% đã thanh toán

        return [
            'employee_id' => $employee->id,
            'pay_period_start' => $payPeriodStart,
            'pay_period_end' => $payPeriodEnd,
            'base_salary' => $baseSalary,
            'allowances' => $allowances,
            'deductions' => $deductions,
            'bonus' => $bonus,
            'fines' => $fines,
            'net_salary' => $netSalary,
            'status' => $isPaid ? 'paid' : (fake()->boolean(90) ? 'pending' : 'cancelled'),
            'paid_date' => $isPaid ? $payPeriodEnd->copy()->addDays(fake()->numberBetween(5, 10)) : null, // Thanh toán sau khi kết thúc kỳ 5-10 ngày
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }
}