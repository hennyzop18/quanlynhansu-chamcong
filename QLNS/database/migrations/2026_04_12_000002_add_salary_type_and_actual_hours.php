<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * M1 + M2 + M3: Mở rộng hệ thống tính lương hỗ trợ 3 chế độ:
 *   ① fixed   — Lương cố định/tháng (có thể trừ ngày vắng)
 *   ② hourly  — Tính theo giờ thực tế × đơn giá/giờ
 *   ③ override — Admin ghi đè mức lương đồng loạt khi tạo bảng lương
 */
return new class extends Migration {
    public function up(): void
    {
        // ─────────────────────────────────────────────────────────────────
        // M1: employees — loại lương & đơn giá giờ
        // ─────────────────────────────────────────────────────────────────
        Schema::table('employees', function (Blueprint $table) {
            $table->enum('salary_type', ['fixed', 'hourly'])
                ->default('fixed')
                ->after('dependents')
                ->comment('Chế độ tính lương: fixed = lương cố định, hourly = theo giờ');

            $table->decimal('hourly_rate', 15, 2)
                ->nullable()
                ->after('salary_type')
                ->comment('Đơn giá mỗi giờ làm (VND). Chỉ dùng khi salary_type = hourly');
        });

        // ─────────────────────────────────────────────────────────────────
        // M2: attendances — số giờ thực tế mỗi ngày
        // ─────────────────────────────────────────────────────────────────
        Schema::table('attendances', function (Blueprint $table) {
            $table->decimal('actual_hours', 5, 2)
                ->nullable()
                ->after('check_out_time')
                ->comment(
                    'Số giờ làm thực tế trong ngày = (check_out - check_in) - break_hours. ' .
                    'Tối đa 8h/ngày (phần thừa là overtime, chưa hỗ trợ). ' .
                    'NULL khi chưa check-out.'
                );
        });

        // ─────────────────────────────────────────────────────────────────
        // M3: salaries — snapshot + tổng giờ cho lương theo giờ
        // ─────────────────────────────────────────────────────────────────
        Schema::table('salaries', function (Blueprint $table) {
            // Snapshot loại lương tại thời điểm tính (bảo toàn lịch sử dù NV đổi loại sau đó)
            $table->enum('salary_type', ['fixed', 'hourly', 'override'])
                ->default('fixed')
                ->after('employee_id')
                ->comment('Snapshot chế độ lương lúc tính: fixed | hourly | override');

            // Dùng cho chế độ hourly
            $table->decimal('total_actual_hours', 8, 2)
                ->default(0)
                ->after('salary_type')
                ->comment('Tổng giờ làm thực tế trong kỳ (SUM của attendances.actual_hours)');

            $table->decimal('hourly_rate_snapshot', 15, 2)
                ->nullable()
                ->after('total_actual_hours')
                ->comment('Snapshot đơn giá/giờ tại thời điểm tính lương');

            $table->decimal('ot_hours', 8, 2)
                ->default(0)
                ->after('hourly_rate_snapshot')
                ->comment('Số giờ OT (Admin nhập thêm hoặc từ module OT sau này)');

            // Dùng cho chế độ override (admin ghi đè đồng loạt)
            $table->decimal('default_salary_override', 15, 2)
                ->nullable()
                ->after('hourly_rate_snapshot')
                ->comment('Mức lương admin ghi đè khi chọn chế độ "batch default". NULL = không override');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['salary_type', 'hourly_rate']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn('actual_hours');
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->dropColumn([
                'salary_type',
                'total_actual_hours',
                'hourly_rate_snapshot',
                'ot_hours',
                'default_salary_override',
            ]);
        });
    }
};
