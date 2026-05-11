<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rewards_discipline', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade'); // Liên kết nhân viên
            $table->enum('type', ['reward', 'discipline']); // Loại: khen thưởng hay kỷ luật
            $table->date('date'); // Ngày ghi nhận
            $table->text('reason'); // Lý do
            $table->decimal('amount', 15, 2)->nullable(); // Số tiền (nếu có, ảnh hưởng lương)
            $table->timestamps();

            $table->index('employee_id'); // Index theo nhân viên
            $table->index('type');        // Index theo loại
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rewards_discipline');
    }
};