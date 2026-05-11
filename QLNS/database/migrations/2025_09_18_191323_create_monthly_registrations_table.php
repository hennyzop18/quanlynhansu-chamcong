<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('monthly_registrations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
        $table->foreignId('work_schedule_id')->constrained('work_schedules')->onDelete('cascade');
        $table->date('date'); // Ngày làm việc cụ thể
        $table->decimal('scheduled_hours', 4, 2); // Số giờ của ca làm việc đã đăng ký
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Trạng thái phê duyệt
        $table->timestamps();

        // Đảm bảo một nhân viên chỉ có thể đăng ký một ca cho một ngày
        $table->unique(['employee_id', 'date']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_registrations');
    }
};
