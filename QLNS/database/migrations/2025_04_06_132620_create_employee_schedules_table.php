<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Định nghĩa cấu trúc bảng employee_schedules.
     */
    public function up(): void
    {
        // Tạo bảng mới tên là 'employee_schedules'
        Schema::create('employee_schedules', function (Blueprint $table) {
            $table->id(); // Cột ID tự tăng, khóa chính

            // Khóa ngoại liên kết đến bảng employees
            $table->foreignId('employee_id')
                ->constrained('employees') // Ràng buộc khóa ngoại tới bảng 'employees'
                ->onDelete('cascade'); // Nếu xóa employee thì xóa luôn các schedule liên quan

            // Khóa ngoại liên kết đến bảng work_schedules
            $table->foreignId('work_schedule_id')
                ->constrained('work_schedules') // Ràng buộc khóa ngoại tới bảng 'work_schedules'
                ->onDelete('cascade'); // Nếu xóa work_schedule thì xóa luôn các gán lịch này

            $table->date('start_date'); // Ngày bắt đầu áp dụng lịch làm việc này
            $table->date('end_date')->nullable(); // Ngày kết thúc áp dụng (nullable nghĩa là có thể trống, cho biết lịch đang áp dụng vô thời hạn)

            $table->timestamps(); // Tự động tạo cột created_at và updated_at

            // (Tùy chọn) Thêm các index để tăng tốc độ truy vấn
            $table->index(['employee_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     * Định nghĩa hành động khi rollback (xóa bảng).
     */
    public function down(): void
    {
        // Xóa bảng nếu migration bị rollback
        Schema::dropIfExists('employee_schedules');
    }
};