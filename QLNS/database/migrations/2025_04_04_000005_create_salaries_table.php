<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Nếu xóa NV thì xóa luôn lương? Hoặc dùng set null nếu muốn giữ lại
            $table->date('pay_period_start'); // Ngày bắt đầu kỳ lương
            $table->date('pay_period_end');   // Ngày kết thúc kỳ lương
            $table->decimal('base_salary', 15, 2); // Lương cơ bản, ví dụ 15 chữ số tổng, 2 chữ số thập phân
            $table->decimal('allowances', 15, 2)->default(0);  // Phụ cấp
            $table->decimal('deductions', 15, 2)->default(0);  // Khấu trừ
            $table->decimal('bonus', 15, 2)->default(0);       // Thưởng từ khen thưởng
            $table->decimal('fines', 15, 2)->default(0);        // Phạt từ kỷ luật
            $table->decimal('net_salary', 15, 2);    // Lương thực nhận (có thể tính toán khi tạo)
            $table->enum('status', ['pending', 'paid', 'cancelled'])->default('pending'); // Trạng thái thanh toán
            $table->date('paid_date')->nullable(); // Ngày thanh toán
            $table->text('notes')->nullable();     // Ghi chú
            $table->timestamps();

            // Index để query nhanh theo nhân viên và kỳ lương
            $table->index(['employee_id', 'pay_period_start', 'pay_period_end']);
            // Đảm bảo không có 2 record lương trùng kỳ cho 1 nhân viên
            $table->unique(['employee_id', 'pay_period_start', 'pay_period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
