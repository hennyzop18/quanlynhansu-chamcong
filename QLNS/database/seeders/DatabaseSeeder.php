<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            PositionSeeder::class,
            DepartmentSeeder::class,
            WorkScheduleSeeder::class, // Thêm WorkSchedule Seeder
            EmployeeSeeder::class,
            AttendanceSeeder::class,
            RewardDisciplineSeeder::class, // Thêm Reward/Discipline Seeder
            SalarySeeder::class,        // Thêm Salary Seeder (chạy sau Employee)
        ]);
    }
}