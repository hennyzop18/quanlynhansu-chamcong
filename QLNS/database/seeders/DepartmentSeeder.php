<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::create(['name' => 'Ban Giám Đốc']);
        Department::create(['name' => 'Phòng Nhân sự']);
        Department::create(['name' => 'Phòng Kế toán']);
        Department::create(['name' => 'Phòng IT']);
        Department::create(['name' => 'Phòng Kinh doanh']);
        // Department::factory(5)->create(); // Tạo 5 phòng ban ngẫu nhiên
    }
}