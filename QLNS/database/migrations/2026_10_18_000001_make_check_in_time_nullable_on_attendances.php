<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * check_in_time phải cho phép NULL vì:
 *  - SalaryController::syncLeavesToAttendance tạo bản ghi 'leave' cho ngày nghỉ phép chưa có chấm công;
 *  - Admin được phép xoá giờ check-in khi sửa chấm công.
 * Trước đây cột là NOT NULL nên tính lương bị rollback khi có đơn nghỉ phép đã duyệt.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dateTime('check_in_time')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dateTime('check_in_time')->nullable(false)->change();
        });
    }
};
