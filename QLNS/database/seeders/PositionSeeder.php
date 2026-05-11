<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo các chức vụ cụ thể
        Position::create(['name' => 'Giám đốc', 'description' => 'Quản lý chung']);
        Position::create(['name' => 'Trưởng phòng', 'description' => 'Quản lý phòng ban']);
        Position::create(['name' => 'Nhân viên Marketing', 'description' => 'Thực hiện các chiến dịch marketing']);
        Position::create(['name' => 'Lập trình viên', 'description' => 'Phát triển phần mềm']);
        Position::create(['name' => 'Kế toán viên', 'description' => 'Xử lý các nghiệp vụ kế toán']);

        // Hoặc tạo bằng factory
        // Position::factory(10)->create(); // Tạo 10 chức vụ ngẫu nhiên
    }
}