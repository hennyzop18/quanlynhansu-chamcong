<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    public function definition(): array
    {
        // Danh sách phòng ban mẫu
        $departments = ['Nhân sự', 'Kế toán', 'Kinh doanh', 'Marketing', 'Kỹ thuật', 'IT', 'Sản xuất'];
        return [
            'name' => fake()->unique()->randomElement($departments), // Chọn ngẫu nhiên và duy nhất
            'description' => fake()->bs(), // Mô tả ngẫu nhiên
        ];
    }
}