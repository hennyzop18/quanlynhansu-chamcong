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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('dob')->nullable(); // Ngày sinh có thể null
            $table->string('phone_number')->nullable();
            $table->string('personal_email')->nullable();
            $table->text('address')->nullable();
            $table->date('hire_date');
            $table->date('termination_date')->nullable();
            $table->foreignId('position_id')->constrained('positions'); // Liên kết chức vụ
            $table->foreignId('department_id')->nullable()->constrained('departments'); // Nếu có bảng departments
            $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
            $table->softDeletes(); // Thêm soft delete
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
