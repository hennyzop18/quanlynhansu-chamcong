<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * DESIGN#9 + DESIGN#10: Thêm các cột cần thiết cho tính lương hiện đại
 * - employees: base_salary, allowances, insurance_salary, dependents
 * - work_schedules: break_hours
 * - salaries: thêm các cột chi tiết tính lương bắt buộc VN
 */
return new class extends Migration {
    public function up(): void
    {
        // --- employees: Thêm thông tin lương & bảo hiểm ---
        Schema::table('employees', function (Blueprint $table) {
            // Lương cơ bản (gross) của nhân viên, thay thế hardcode 5,000,000
            $table->decimal('base_salary', 15, 2)->default(5000000)->after('status')
                ->comment('Lương cơ bản (gross) hàng tháng của nhân viên');
            
            // Phụ cấp chịu thuế hàng tháng (đi lại, trách nhiệm, thưởng...)
            $table->decimal('taxable_allowances', 15, 2)->default(0)->after('base_salary')
                ->comment('Tổng phụ cấp chịu thuế hàng tháng');

            // Phụ cấp không chịu thuế hàng tháng (ăn ca, trang phục)
            $table->decimal('nontaxable_allowances', 15, 2)->default(0)->after('taxable_allowances')
                ->comment('Tổng phụ cấp KHÔNG chịu thuế hàng tháng');
            
            // Mức lương đóng bảo hiểm (thường = lương ghi trong HĐLĐ, có thể khác base_salary)
            // Bị giới hạn ở 20 lần mức lương tối thiểu vùng theo luật VN
            $table->decimal('insurance_salary', 15, 2)->nullable()->after('taxable_allowances')
                ->comment('Mức lương làm căn cứ đóng BHXH (ghi trong HĐLĐ, nullable = dùng base_salary)');
            
            // Số người phụ thuộc (giảm trừ gia cảnh PIT: 4,400,000 VND/người)
            $table->unsignedTinyInteger('dependents')->default(0)->after('insurance_salary')
                ->comment('Số người phụ thuộc - dùng tính giảm trừ gia cảnh thuế TNCN');
        });

        // --- work_schedules: Thêm giờ nghỉ (thay thế string matching DESIGN#10) ---
        Schema::table('work_schedules', function (Blueprint $table) {
            $table->decimal('break_hours', 4, 2)->default(0)->after('late_threshold')
                ->comment('Giờ nghỉ giữa ca (vd: 1.0 cho ca hành chính). Thay thế string matching tên ca.');
        });

        // --- salaries: Thêm các cột chia nhỏ chi tiết tính lương hiện đại ---
        Schema::table('salaries', function (Blueprint $table) {
            // Số ngày công chuẩn và thực tế
            $table->unsignedSmallInteger('standard_work_days')->default(22)->after('base_salary')
                ->comment('Số ngày công chuẩn của tháng (22 ngày mặc định)');
            $table->unsignedSmallInteger('actual_work_days')->default(0)->after('standard_work_days')
                ->comment('Số ngày công thực tế (present + late)');
            $table->unsignedSmallInteger('absent_days')->default(0)->after('actual_work_days')
                ->comment('Số ngày vắng mặt (absent)');
            $table->unsignedSmallInteger('late_days')->default(0)->after('absent_days')
                ->comment('Số ngày đi trễ');

            // Lương tính theo công
            $table->decimal('prorated_salary', 15, 2)->default(0)->after('late_days')
                ->comment('Lương được tính theo ngày công thực tế = base_salary / standard_work_days * actual_work_days');

            // Đổi tên cột allowances thành taxable_allowances
            $table->renameColumn('allowances', 'taxable_allowances');

            // Phụ cấp không chịu thuế (lưu lại snapshot)
            $table->decimal('nontaxable_allowances', 15, 2)->default(0)->after('taxable_allowances')
                ->comment('Phụ cấp không chịu thuế (chỉ cộng vào Gross, không chịu thuế TNCN)');

            // Khấu trừ bảo hiểm (tách riêng từng khoản)
            $table->decimal('si_deduction', 15, 2)->default(0)->after('nontaxable_allowances')
                ->comment('Khấu trừ BHXH nhân viên đóng (8% mức lương bảo hiểm)');
            $table->decimal('hi_deduction', 15, 2)->default(0)->after('si_deduction')
                ->comment('Khấu trừ BHYT nhân viên đóng (1.5% mức lương bảo hiểm)');
            $table->decimal('ui_deduction', 15, 2)->default(0)->after('hi_deduction')
                ->comment('Khấu trừ BHTN nhân viên đóng (1% mức lương bảo hiểm)');

            // Thuế TNCN
            $table->decimal('taxable_income', 15, 2)->default(0)->after('ui_deduction')
                ->comment('Thu nhập chịu thuế = Gross - Bảo hiểm - Giảm trừ cá nhân - Giảm trừ gia cảnh');
            $table->decimal('pit_tax', 15, 2)->default(0)->after('taxable_income')
                ->comment('Thuế TNCN phải nộp (computed từ taxable_income theo biểu thuế lũy tiến 7 bậc)');
            
            // Phạt đi trễ / về sớm (tách khỏi fines = kỷ luật)
            $table->decimal('late_fine', 15, 2)->default(0)->after('pit_tax')
                ->comment('Tiền phạt đi trễ (nếu có quy định, vd: trừ theo số phút hoặc số ngày)');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['base_salary', 'taxable_allowances', 'nontaxable_allowances', 'insurance_salary', 'dependents']);
        });

        Schema::table('work_schedules', function (Blueprint $table) {
            $table->dropColumn('break_hours');
        });

        Schema::table('salaries', function (Blueprint $table) {
            $table->renameColumn('taxable_allowances', 'allowances');
            $table->dropColumn([
                'standard_work_days', 'actual_work_days', 'absent_days', 'late_days',
                'prorated_salary', 'nontaxable_allowances', 'si_deduction', 'hi_deduction', 'ui_deduction',
                'taxable_income', 'pit_tax', 'late_fine',
            ]);
        });
    }
};
