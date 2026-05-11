<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Position;
use App\Models\Department;
use App\Models\User; // Import User
use Carbon\Carbon;

class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $hireDate = fake()->dateTimeBetween('-5 years', 'now'); // Ngày vào làm trong 5 năm qua
        $terminationDate = fake()->optional(0.1) // 10% khả năng đã nghỉ việc
            ->dateTimeBetween($hireDate, 'now');

        return [
            'employee_code' => 'NV' . fake()->unique()->numberBetween(1000, 9999),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'dob' => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'phone_number' => fake()->phoneNumber(),
            'personal_email' => fake()->unique()->safeEmail(),
            'address' => fake()->address(),
            'hire_date' => $hireDate->format('Y-m-d'),
            'termination_date' => $terminationDate ? $terminationDate->format('Y-m-d') : null,
            'position_id' => Position::inRandomOrder()->first()->id ?? Position::factory(), // Lấy Position ngẫu nhiên hoặc tạo mới
            'department_id' => Department::inRandomOrder()->first()->id ?? Department::factory(), // Lấy Department ngẫu nhiên hoặc tạo mới
            'status' => $terminationDate ? 'terminated' : (fake()->boolean(95) ? 'active' : 'inactive'), // 95% active nếu chưa nghỉ
            // Thêm lương cơ bản mẫu nếu bạn đã thêm cột vào model/migration
            // 'base_salary' => fake()->numberBetween(8000000, 30000000),
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (\App\Models\Employee $employee) {
            // Tự động tạo User account cho mỗi Employee được tạo bởi factory
            if ($employee->status !== 'terminated') { // Chỉ tạo user cho NV chưa nghỉ
                User::factory()->employeeUser($employee)->create([
                    'email' => 'emp' . $employee->employee_code . '@example.com', // Tạo email user theo mã NV
                ]);
            }
        });
    }
}