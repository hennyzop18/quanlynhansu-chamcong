<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RewardDiscipline;

class RewardDisciplineSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo khoảng 100 record khen thưởng/kỷ luật mẫu
        RewardDiscipline::factory(100)->create();
    }
}