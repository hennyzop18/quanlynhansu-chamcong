<?php
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com', // Thay bằng email admin thực tế
            'password' => Hash::make('password'), // Thay bằng mật khẩu mạnh
        ]);

        // Có thể tạo thêm Admin khác nếu muốn
        // User::factory()->admin()->create([...]);
    }
}