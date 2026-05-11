<?php
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Employee;

class RewardDisciplineFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['reward', 'discipline']);
        $amount = null;
        if (fake()->boolean(70)) { // 70% có số tiền
            $amount = $type === 'reward'
                ? fake()->numberBetween(200000, 5000000)
                : fake()->numberBetween(50000, 1000000);
        }

        return [
            'employee_id' => Employee::where('status', 'active')->inRandomOrder()->first()->id ?? Employee::factory(),
            'type' => $type,
            'date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'reason' => fake()->sentence(10),
            'amount' => $amount,
        ];
    }
}