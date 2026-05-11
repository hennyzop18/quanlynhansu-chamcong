<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'salary_type',
        'total_actual_hours',
        'ot_hours',
        'hourly_rate_snapshot',
        'default_salary_override',
        'pay_period_start',
        'pay_period_end',
        'base_salary',
        'standard_work_days',
        'actual_work_days',
        'absent_days',
        'late_days',
        'prorated_salary',
        'taxable_allowances',
        'nontaxable_allowances',
        'si_deduction',
        'hi_deduction',
        'ui_deduction',
        'deductions',
        'taxable_income',
        'pit_tax',
        'bonus',
        'fines',
        'late_fine',
        'net_salary',
        'status',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'pay_period_start'        => 'date',
        'pay_period_end'          => 'date',
        'paid_date'               => 'date',
        'total_actual_hours'      => 'decimal:2',
        'ot_hours'                => 'decimal:2',
        'hourly_rate_snapshot'    => 'decimal:2',
        'default_salary_override' => 'decimal:2',
        'base_salary'             => 'decimal:2',
        'prorated_salary'         => 'decimal:2',
        'taxable_allowances'      => 'decimal:2',
        'nontaxable_allowances'   => 'decimal:2',
        'si_deduction'            => 'decimal:2',
        'hi_deduction'            => 'decimal:2',
        'ui_deduction'            => 'decimal:2',
        'deductions'              => 'decimal:2',
        'taxable_income'          => 'decimal:2',
        'pit_tax'                 => 'decimal:2',
        'bonus'                   => 'decimal:2',
        'fines'                   => 'decimal:2',
        'late_fine'               => 'decimal:2',
        'net_salary'              => 'decimal:2',
        'standard_work_days'      => 'integer',
        'actual_work_days'        => 'integer',
        'absent_days'             => 'integer',
        'late_days'               => 'integer',
    ];


    /**
     * Lấy thông tin nhân viên liên quan đến bản ghi lương này.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Accessor cho Tháng
    public function getMonthAttribute()
    {
        return $this->pay_period_start ? $this->pay_period_start->month : null;
    }

    // Accessor cho Năm
    public function getYearAttribute()
    {
        return $this->pay_period_start ? $this->pay_period_start->year : null;
    }

    // Accessor cho Tổng Thu Nhập (Gross) - Cải tiến để tránh bị 0 VNĐ
    public function getTotalGrossAttribute()
    {
        $base = 0;
        if ($this->salary_type === 'hourly') {
            $base = (($this->total_actual_hours ?? 0) + ($this->ot_hours ?? 0) * 1.5) * ($this->hourly_rate_snapshot ?? 0);
        } elseif ($this->salary_type === 'override') {
            $base = ($this->default_salary_override ?? 0);
        } else {
            // Với lương cố định, ưu tiên lấy prorated_salary (nếu > 0), nếu không thì lấy base_salary
            $base = ($this->prorated_salary > 0) ? $this->prorated_salary : ($this->base_salary ?? 0);
        }

        return $base + ($this->taxable_allowances ?? 0) + ($this->nontaxable_allowances ?? 0) + ($this->bonus ?? 0);
    }

    // Accessor tính tổng các khoản khấu trừ
    public function getTotalDeductionsAttribute()
    {
        return ($this->si_deduction ?? 0) + ($this->hi_deduction ?? 0) + ($this->ui_deduction ?? 0) 
               + ($this->pit_tax ?? 0) + ($this->fines ?? 0) + ($this->late_fine ?? 0);
    }
}