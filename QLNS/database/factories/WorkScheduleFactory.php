<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class WorkScheduleFactory extends Factory
{
    public function definition(): array
    {
        $startTime = Carbon::createFromTime(fake()->randomElement([8, 9]), fake()->randomElement([0, 15, 30])); // Giờ bắt đầu 8h, 8h15, 8h30, 9h...
        $endTime = $startTime->copy()->addHours(8)->addMinutes(fake()->randomElement([0, 30])); // Kết thúc sau 8-8.5 tiếng
        $lateThreshold = $startTime->copy()->addMinutes(fake()->randomElement([15, 30]));

        return [
            'name' => 'Ca ' . fake()->unique()->randomElement(['Hành chính', 'Sáng', 'Chiều', 'Tối', 'Linh hoạt']),
            'start_time' => $startTime->format('H:i:s'),
            'end_time' => $endTime->format('H:i:s'),
            'late_threshold' => fake()->boolean(80) ? $lateThreshold->format('H:i:s') : null, // 80% có mốc đi trễ
        ];
    }
}