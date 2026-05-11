<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    public function definition(): array
    {
        $date = fake()->dateTimeBetween('-1 month', 'now')->format('Y-m-d');
        $checkInDateTime = Carbon::parse($date . ' ' . fake()->randomElement(['08:00:00', '08:10:00', '07:55:00', '08:30:00']))
            ->addMinutes(fake()->numberBetween(-5, 15)); // Giờ checkin linh động quanh 8h

        $checkOutDateTime = fake()->optional(0.95) // 95% có checkout
            ->dateTimeBetween($checkInDateTime->copy()->addHours(7.5), $checkInDateTime->copy()->addHours(9)); // Checkout sau 7.5-9 tiếng

        return [
            'employee_id' => Employee::inRandomOrder()->where('status', 'active')->first()->id ?? Employee::factory(),
            'date' => $date,
            'check_in_time' => $checkInDateTime,
            'check_out_time' => $checkOutDateTime,
            // Logic xác định status (present, late) cần phức tạp hơn, dựa vào WorkSchedule
            'status' => $checkOutDateTime ? ($checkInDateTime->format('H:i') > '08:15' ? 'late' : 'present') : null, // Ví dụ đơn giản
            'notes' => fake()->optional(0.1)->sentence(), // 10% có ghi chú
        ];
    }
}