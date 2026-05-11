<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Salary;
use Carbon\Carbon;

class DeleteSalaries extends Command
{
    /**
     * Tên lệnh: salary:delete {tháng} {năm}
     */
    protected $signature = 'salary:delete {month} {year}';

    protected $description = 'Xóa dữ liệu lương của một tháng và năm cụ thể';

    public function handle()
    {
        $month = $this->argument('month');
        $year = $this->argument('year');

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $count = Salary::where('pay_period_start', $startOfMonth)
                       ->where('pay_period_end', $endOfMonth)
                       ->count();

        if ($count === 0) {
            $this->info("Không tìm thấy dữ liệu lương cho Tháng {$month}/{$year}.");
            return;
        }

        if ($this->confirm("Bạn có chắc chắn muốn xóa {$count} bản ghi lương của Tháng {$month}/{$year} không?")) {
            Salary::where('pay_period_start', $startOfMonth)
                  ->where('pay_period_end', $endOfMonth)
                  ->delete();

            $this->info("Đã xóa thành công dữ liệu lương Tháng {$month}/{$year}.");
        }
    }
}
