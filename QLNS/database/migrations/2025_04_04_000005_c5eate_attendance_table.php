<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Cascade delete nếu nhân viên bị xóa
            $table->dateTime('check_in_time');
            $table->dateTime('check_out_time')->nullable();
            $table->date('date');
            $table->enum('status', ['present', 'late', 'absent', 'leave'])->nullable(); // Sẽ cần logic để xác định
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('date'); // Index cột date để query nhanh
            $table->unique(['employee_id', 'date']); // Đảm bảo mỗi nhân viên chỉ có 1 record/ngày
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};