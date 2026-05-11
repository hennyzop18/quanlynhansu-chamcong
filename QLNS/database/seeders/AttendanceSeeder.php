<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Khai báo một bộ đếm và số lượng giới hạn
        $recordsCreated = 0;
        $limit = 200;

        $employees = Employee::where('status', 'active')->pluck('id');
        if ($employees->isEmpty()) {
            $this->command->info('No active employees found to seed attendance for.');
            return; // Không có nhân viên thì dừng lại
        }

        $startDate = Carbon::now()->subDays(30);
        $endDate = Carbon::now();

        foreach ($employees as $employeeId) {
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // 3. Kiểm tra nếu đã đủ số lượng thì thoát khỏi hàm
                if ($recordsCreated >= $limit) {
                    $this->command->info("Successfully created $recordsCreated attendance records.");
                    return; // Thoát hoàn toàn khỏi hàm run()
                }

                if (fake()->boolean(85)) {
                    $startTime = Carbon::parse($date->toDateString() . ' ' . fake()->randomElement(['08:00:00', '07:50:00', '08:10:00', '08:25:00']));
                    $startTime->addMinutes(fake()->numberBetween(-5, 10));

                    $endTime = null;
                    if (fake()->boolean(95)) {
                        $endTime = $startTime->copy()->addHours(fake()->numberBetween(8, 9))->addMinutes(fake()->numberBetween(0, 59));
                    }

                    Attendance::updateOrCreate(
                        [
                            'employee_id' => $employeeId,
                            'date' => $date->toDateString()
                        ],
                        [
                            'check_in_time' => $startTime,
                            'check_out_time' => $endTime,
                            'status' => $endTime ? ($startTime->format('H:i') > '08:15' ? 'late' : 'present') : null,
                            'notes' => fake()->optional(0.05)->sentence()
                        ]
                    );

                    // 2. Tăng bộ đếm sau mỗi lần tạo thành công
                    $recordsCreated++;
                }
            }
        }
        $this->command->info("Successfully created $recordsCreated attendance records.");
    }
}