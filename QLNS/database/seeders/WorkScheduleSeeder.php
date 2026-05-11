<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\WorkSchedule;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo ca hành chính chuẩn
        WorkSchedule::create([
            'name' => 'Hành chính (8h-17h)',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00', // Giả sử nghỉ trưa 1 tiếng
            'late_threshold' => '08:15:00',
        ]);
        WorkSchedule::create([
            'name' => 'Ca Sáng (6h-14h)',
            'start_time' => '06:00:00',
            'end_time' => '14:00:00',
            'late_threshold' => '06:10:00',
        ]);
        WorkSchedule::create([
            'name' => 'Ca Chiều (14h-22h)',
            'start_time' => '14:00:00',
            'end_time' => '22:00:00',
            'late_threshold' => '14:10:00',
        ]);
        // Có thể tạo thêm bằng factory
        // WorkSchedule::factory(3)->create();
    }
}