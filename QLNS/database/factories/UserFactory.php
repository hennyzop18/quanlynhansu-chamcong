<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Employee; // Import Employee

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'), // Mật khẩu mặc định là 'password'
            'remember_token' => Str::random(10),
            'role' => 'employee', // Mặc định tạo user là employee
            'employee_id' => null, // Sẽ được gán trong EmployeeFactory hoặc Seeder
        ];
    }

    // State để tạo Admin
    public function admin(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'admin',
            'employee_id' => null, // Admin không cần liên kết Employee
        ]);
    }

    // State để tạo Employee User (có thể gọi sau khi Employee được tạo)
    public function employeeUser(Employee $employee): static
    {
        return $this->state(fn(array $attributes) => [
            'name' => $employee->full_name, // Lấy tên từ Employee
            'role' => 'employee',
            'employee_id' => $employee->id,
        ]);
    }


    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}