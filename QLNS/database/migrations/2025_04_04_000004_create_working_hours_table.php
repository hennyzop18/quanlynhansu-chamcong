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
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên ca làm việc (VD: Hành chính, Ca sáng, Ca tối)
            $table->time('start_time'); // Giờ bắt đầu
            $table->time('end_time');   // Giờ kết thúc
            $table->time('late_threshold')->nullable(); // Mốc thời gian bắt đầu tính đi trễ (vd: 08:15)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
