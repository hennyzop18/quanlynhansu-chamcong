<?php

namespace App\Exports;

use App\Models\Salary;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalariesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return Salary::with(['employee.department', 'employee.position'])
            ->whereMonth('pay_period_start', $this->month)
            ->whereYear('pay_period_start', $this->year)
            ->get();
    }

    public function headings(): array
    {
        return [
            'Mã NV',
            'Họ tên',
            'Phòng ban',
            'Chức vụ',
            'Công chuẩn',
            'Công thực',
            'Lương cơ bản',
            'Phụ cấp chịu thuế',
            'Phụ cấp miễn thuế',
            'OT',
            'Thưởng',
            'Kỷ luật',
            'Bảo hiểm',
            'Thuế TNCN',
            'Lương NET',
            'Trạng thái',
        ];
    }

    public function map($salary): array
    {
        return [
            $salary->employee->employee_code,
            $salary->employee->full_name,
            $salary->employee->department->name ?? '--',
            $salary->employee->position->name ?? '--',
            $salary->standard_work_days,
            $salary->actual_work_days,
            $salary->base_salary,
            $salary->taxable_allowances_snapshot,
            $salary->nontaxable_allowances_snapshot,
            $salary->ot_amount,
            $salary->bonus,
            $salary->fines,
            $salary->insurance_deduction,
            $salary->pit_amount,
            $salary->net_salary,
            $salary->status == 'paid' ? 'Đã thanh toán' : 'Chờ duyệt',
        ];
    }
}
