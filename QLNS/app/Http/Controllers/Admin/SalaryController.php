<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salary;
use App\Models\Employee;
use App\Models\RewardDiscipline; // Để lấy thưởng/phạt
use App\Models\Attendance; // Để lấy thông tin chấm công
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // Sử dụng transaction
use App\Http\Requests\StoreSalaryRequest; // Sử dụng Form Request
use App\Http\Requests\UpdateSalaryRequest; // Sử dụng Form Request
use App\Models\WorkSchedule; // Có thể cần để tính công
use Illuminate\Support\Facades\Log;

class SalaryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Lọc theo kỳ lương (tháng/năm), nhân viên
        $filterMonth = $request->input('month', now()->month);
        $filterYear = $request->input('year', now()->year);
        $filterEmployee = $request->input('employee_id');

        $query = Salary::with('employee')
            ->whereYear('pay_period_start', $filterYear)
            ->whereMonth('pay_period_start', $filterMonth)
            ->latest(); // Sắp xếp theo id giảm dần hoặc ngày tạo

        if ($filterEmployee) {
            $query->where('employee_id', $filterEmployee);
        }

        $salaries = $query->paginate(20);
        $employees = Employee::where('status', 'active')->orderBy('first_name')->get();

        return view('admin.salaries.index', compact('salaries', 'employees', 'filterMonth', 'filterYear', 'filterEmployee'));
    }

    /**
     * Show the form for creating a new resource (Thường là form để chọn kỳ lương).
     */
    public function create()
    {
        // Hiển thị form chọn tháng/năm để tạo bảng lương
        return view('admin.salaries.create');
    }

    /**
     * Store a newly created salary batch for a given pay period.
     *
     * Hỗ trợ 3 chế độ tính gross:
     *   ① fixed   — base_salary / standard_days × actual_days (prorate theo ngày công)
     *   ② hourly  — SUM(actual_hours) × hourly_rate
     *   ③ override — Admin nhập 1 mức cố định áp dụng cho tất cả NV (bỏ qua salary_type nhân viên)
     *
     * Cả 3 chế độ đều tính:
     *   + Phụ cấp (allowances)
     *   + Thưởng/Kỷ luật (trong kỳ, filter đúng type)
     *   - BHXH 8% + BHYT 1.5% + BHTN 1% = 10.5% trên insurance_salary
     *   - Thuế TNCN lũy tiến 7 bậc
     */
    public function store(StoreSalaryRequest $request)
    {
        $validated = $request->validated();

        $month          = $validated['month'];
        $year           = $validated['year'];
        $payPeriodStart = Carbon::create($year, $month, 1)->startOfMonth();
        $payPeriodEnd   = $payPeriodStart->copy()->endOfMonth();

        // ── Chế độ Override: admin nhập mức lương ghi đè cho tất cả ──
        $overrideSalary = isset($validated['override_salary']) && $validated['override_salary'] > 0
            ? (float) $validated['override_salary']
            : null;

        // ── Số ngày công chuẩn (admin có thể điều chỉnh nếu tháng Tết...) ──
        $STANDARD_WORK_DAYS  = (int) ($validated['standard_work_days'] ?? config('hr.payroll.standard_work_days', 22));
        $PERSONAL_ALLOWANCE  = config('hr.payroll.personal_allowance',  11_000_000);
        $DEPENDENT_ALLOWANCE = config('hr.payroll.dependent_allowance',  4_400_000);
        $LATE_FINE_PER_DAY   = config('hr.payroll.late_fine_per_day',    0);

        $employees = Employee::where('status', 'active')
            ->where('hire_date', '<=', $payPeriodEnd)
            ->where(function ($q) use ($payPeriodStart) {
                $q->whereNull('termination_date')
                  ->orWhere('termination_date', '>=', $payPeriodStart);
            })
            ->get();

        if ($employees->isEmpty()) {
            return redirect()->route('admin.salaries.create')
                ->with('error', "Không có nhân viên nào đủ điều kiện tính lương cho kỳ {$month}/{$year}.");
        }

        DB::beginTransaction();
        try {
            $processedCount = 0;

            foreach ($employees as $employee) {
                // Đồng bộ đơn nghỉ phép vào bảng chấm công để tính ngày công đúng
                $this->syncLeavesToAttendance($employee->id, $payPeriodStart->toDateString(), $payPeriodEnd->toDateString());

                // ═══════════════════════════════════════════════════════
                // BƯỚC 1 — XÁC ĐỊNH CHẾ ĐỘ LƯƠNG & TÍNH GROSS
                // ═══════════════════════════════════════════════════════
                $taxableAllowances    = (float) ($employee->taxable_allowances    ?? 0);
                $nontaxableAllowances = (float) ($employee->nontaxable_allowances ?? 0);
                $dependents      = (int)   ($employee->dependents        ?? 0);
                $insuranceSalary = (float) ($employee->insurance_salary > 0 ? $employee->insurance_salary : ($employee->base_salary ?? 5000000));

                // Số liệu chấm công trong kỳ
                $attendanceStats = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$payPeriodStart->toDateString(), $payPeriodEnd->toDateString()])
                    ->selectRaw("
                        COUNT(CASE WHEN status IN ('present', 'late') OR (status = 'leave' AND actual_hours > 0) THEN 1 END) as actual_days,
                        COUNT(CASE WHEN status = 'absent' OR (status = 'leave' AND actual_hours = 0) THEN 1 END) as absent_days,
                        COUNT(CASE WHEN status = 'late' THEN 1 END)               as late_days,
                        COALESCE(SUM(actual_hours), 0)                            as total_hours
                    ")
                    ->first();

                $actualWorkDays  = (int)   ($attendanceStats->actual_days ?? 0);
                $absentDays      = (int)   ($attendanceStats->absent_days ?? 0);
                $lateDays        = (int)   ($attendanceStats->late_days   ?? 0);
                $totalHours      = (float) ($attendanceStats->total_hours ?? 0);

                // Lấy chi tiết các đơn OT đã được phê duyệt trong kỳ
                $approvedOts = \App\Models\OvertimeRequest::where('employee_id', $employee->id)
                    ->where('status', 'approved')
                    ->whereBetween('date', [$payPeriodStart->toDateString(), $payPeriodEnd->toDateString()])
                    ->get();

                $otSalaryTotal = 0;
                $otHoursTotal  = $approvedOts->sum('hours');

                // ── Chọn chế độ & tính gross ──────────────────────────
                $salaryTypeSnapshot    = 'fixed';
                $grossBase             = 0;
                $proratedSalary        = 0;
                $hourlyRateSnapshot    = null;
                $defaultSalaryOverride = null;

                if ($overrideSalary !== null) {
                    $salaryTypeSnapshot    = 'override';
                    $defaultSalaryOverride = $overrideSalary;
                    $proratedSalary        = $overrideSalary;
                    $grossBase             = $overrideSalary;
                } elseif (($employee->salary_type ?? 'fixed') === 'hourly') {
                    $salaryTypeSnapshot = 'hourly';
                    $hourlyRate         = (float) ($employee->hourly_rate ?? 0);
                    $hourlyRateSnapshot = $hourlyRate;
                    $baseHourlySalary   = $totalHours * $hourlyRate;
                    
                    // Tính OT theo hệ số cho nhân viên theo giờ
                    foreach ($approvedOts as $ot) {
                        $multiplier = $this->getOtMultiplier($ot->date);
                        $otSalaryTotal += $ot->hours * $hourlyRate * $multiplier;
                    }
                    
                    $grossBase          = round($baseHourlySalary + $otSalaryTotal, 2);
                    $proratedSalary     = $grossBase;
                } else {
                    $salaryTypeSnapshot = 'fixed';
                    $baseSalary         = (float) ($employee->base_salary ?? 5000000);
                    $dailyRate          = $STANDARD_WORK_DAYS > 0 ? $baseSalary / $STANDARD_WORK_DAYS : 0;
                    $hourlyRate         = $dailyRate / 8; // Giả định 1 ngày công 8 tiếng

                    // Tính lương theo ngày công
                    if ($actualWorkDays >= $STANDARD_WORK_DAYS) {
                        $extraDays = $actualWorkDays - $STANDARD_WORK_DAYS;
                        $proratedSalary = $baseSalary + ($dailyRate * $extraDays * 1.5); // Công thêm tính 1.5x
                    } else {
                        $proratedSalary = $dailyRate * $actualWorkDays;
                    }
                    
                    // Tính OT theo hệ số cho nhân viên lương khoán
                    foreach ($approvedOts as $ot) {
                        $multiplier = $this->getOtMultiplier($ot->date);
                        $otSalaryTotal += $ot->hours * $hourlyRate * $multiplier;
                    }
                    
                    $proratedSalary     = round($proratedSalary + $otSalaryTotal, 2);
                    $grossBase          = $proratedSalary;
                }

                $otHours = $otHoursTotal;
                $otSalary = $otSalaryTotal;

                // ═══════════════════════════════════════════════════════
                // BƯỚC 2 — THƯỞNG, KỶ LUẬT & PHỤ CẤP
                // ═══════════════════════════════════════════════════════
                $rewards = (float) RewardDiscipline::where('employee_id', $employee->id)
                    ->where('type', 'reward')
                    ->whereBetween('date', [$payPeriodStart->toDateString(), $payPeriodEnd->toDateString()])
                    ->sum('amount');

                $fines = (float) RewardDiscipline::where('employee_id', $employee->id)
                    ->where('type', 'discipline')
                    ->whereBetween('date', [$payPeriodStart->toDateString(), $payPeriodEnd->toDateString()])
                    ->sum('amount');

                // Tổng thu nhập chịu thuế (Trước bảo hiểm)
                $taxableEarnings = $proratedSalary + $taxableAllowances + $rewards - $fines;

                // ═══════════════════════════════════════════════════════
                // BƯỚC 3 — TRÍCH ĐÓNG BẢO HIỂM (Mức đóng 10.5%)
                // ═══════════════════════════════════════════════════════
                $grossSalary = $grossBase + $taxableAllowances + $nontaxableAllowances + $rewards;

                // Tính Bảo hiểm (Đảm bảo không bị bằng 0 nếu có lương đóng BH)
                $siDeduction    = round($insuranceSalary * 0.08,  2);
                $hiDeduction    = round($insuranceSalary * 0.015, 2);
                $uiDeduction    = round($insuranceSalary * 0.01,  2);
                $totalInsurance = $siDeduction + $hiDeduction + $uiDeduction;

                // ═══════════════════════════════════════════════════════
                // BƯỚC 5 — THUẾ TNCN (lũy tiến 7 bậc)
                // ═══════════════════════════════════════════════════════
                $taxableIncome = max(0,
                    $grossSalary
                    - $nontaxableAllowances
                    - $totalInsurance
                    - $PERSONAL_ALLOWANCE
                    - ($DEPENDENT_ALLOWANCE * $dependents)
                );
                $pitTax = $this->calculatePIT($taxableIncome);

                // ═══════════════════════════════════════════════════════
                // BƯỚC 6 — PHẠT TRỄ & LƯƠNG NET
                // ═══════════════════════════════════════════════════════
                $lateFine   = $lateDays * $LATE_FINE_PER_DAY;
                $deductions = $totalInsurance + $pitTax;
                $netSalary  = max(0, $grossSalary - $deductions - $fines - $lateFine);

                // Xây dựng chuỗi ghi chú chi tiết OT để đối soát
                $otDetailsList = [];
                foreach ($approvedOts as $ot) {
                    $m = $this->getOtMultiplier($ot->date);
                    $type = ($m == 3.0) ? "Lễ" : (($m == 2.0) ? "Nghỉ" : "Thường");
                    $otDetailsList[] = sprintf("%s (%sx %s): %sh", Carbon::parse($ot->date)->format('d/m'), $m, $type, $ot->hours);
                }
                $otBreakdown = !empty($otDetailsList) ? " | OT: " . implode(", ", $otDetailsList) : "";

                // ═══════════════════════════════════════════════════════
                // BƯỚC 7 — LƯU VÀO DATABASE
                // ═══════════════════════════════════════════════════════
                Salary::updateOrCreate(
                    [
                        'employee_id'      => $employee->id,
                        'pay_period_start' => $payPeriodStart->toDateString(),
                        'pay_period_end'   => $payPeriodEnd->toDateString(),
                    ],
                    [
                        'salary_type'             => $salaryTypeSnapshot,
                        'total_actual_hours'      => $totalHours,
                        'ot_hours'                => $otHours,
                        'hourly_rate_snapshot'    => $hourlyRateSnapshot,
                        'default_salary_override' => $defaultSalaryOverride,
                        'base_salary'             => $employee->base_salary ?? 5_000_000,
                        'standard_work_days'      => $STANDARD_WORK_DAYS,
                        'actual_work_days'         => $actualWorkDays,
                        'absent_days'             => $absentDays,
                        'late_days'               => $lateDays,
                        'prorated_salary'         => $proratedSalary,
                        'taxable_allowances'      => $taxableAllowances,
                        'nontaxable_allowances'   => $nontaxableAllowances,
                        'si_deduction'            => $siDeduction,
                        'hi_deduction'            => $hiDeduction,
                        'ui_deduction'            => $uiDeduction,
                        'deductions'              => $deductions,
                        'taxable_income'          => $taxableIncome,
                        'pit_tax'                 => $pitTax,
                        'bonus'                   => $rewards,
                        'fines'                   => $fines,
                        'late_fine'               => $lateFine,
                        'net_salary'              => $netSalary,
                        'status'                  => 'pending',
                        'notes'                   => sprintf(
                            "[%s] Kỳ %d/%d | %s | Gross: %s%s | BH: %s | Net: %s",
                            strtoupper($salaryTypeSnapshot),
                            $month, $year,
                            $salaryTypeSnapshot === 'hourly'
                                ? "Giờ: {$totalHours}h × " . number_format($hourlyRateSnapshot) . "đ"
                                : "Công: {$actualWorkDays}/{$STANDARD_WORK_DAYS}",
                            number_format($grossSalary),
                            $otBreakdown,
                            number_format($totalInsurance),
                            number_format($netSalary)
                        ),
                    ]
                );

                $processedCount++;
                Log::info("Salary [{$salaryTypeSnapshot}] Employee #{$employee->id}", [
                    'month'       => "{$month}/{$year}",
                    'actual_days' => $actualWorkDays,
                    'total_hours' => $totalHours,
                    'gross'       => $grossSalary,
                    'insurance'   => $totalInsurance,
                    'pit'         => $pitTax,
                    'net'         => $netSalary,
                ]);
            }

            DB::commit();
            return redirect()
                ->route('admin.salaries.index', ['month' => $month, 'year' => $year])
                ->with('success', "Tính lương tháng {$month}/{$year} thành công! ({$processedCount} nhân viên)");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Salary batch error: ' . $e->getMessage(), [
                'file' => $e->getFile(), 'line' => $e->getLine()
            ]);
            return redirect()->route('admin.salaries.create')
                ->with('error', 'Lỗi tính lương: ' . $e->getMessage());
        }
    }

    /**
     * Tính Thuế Thu Nhập Cá Nhân (PIT) theo biểu lũy tiến 7 bậc Việt Nam.
     * Áp dụng cho cá nhân cư trú (Điều 22 Luật Thuế TNCN).
     *
     * Bậc | Thu nhập chịu thuế/tháng  | Thuế suất
     *  1  | Đến 5 triệu               | 5%
     *  2  | Trên 5 - 10 triệu         | 10%
     *  3  | Trên 10 - 18 triệu        | 15%
     *  4  | Trên 18 - 32 triệu        | 20%
     *  5  | Trên 32 - 52 triệu        | 25%
     *  6  | Trên 52 - 80 triệu        | 30%
     *  7  | Trên 80 triệu             | 35%
     *
     * @param float $taxableIncome Thu nhập chịu thuế (VND)
     * @return float Số thuế phải nộp (VND)
     */
    private function calculatePIT(float $taxableIncome): float
    {
        if ($taxableIncome <= 0) return 0;

        $brackets = [
            ['limit' => 5_000_000,  'rate' => 0.05],
            ['limit' => 10_000_000, 'rate' => 0.10],
            ['limit' => 18_000_000, 'rate' => 0.15],
            ['limit' => 32_000_000, 'rate' => 0.20],
            ['limit' => 52_000_000, 'rate' => 0.25],
            ['limit' => 80_000_000, 'rate' => 0.30],
            ['limit' => PHP_INT_MAX, 'rate' => 0.35],
        ];

        $tax       = 0;
        $remaining = $taxableIncome;
        $prev      = 0;

        foreach ($brackets as $bracket) {
            if ($remaining <= 0) break;

            $bracketSize = $bracket['limit'] - $prev;
            $taxable     = min($remaining, $bracketSize);
            $tax        += $taxable * $bracket['rate'];
            $remaining  -= $taxable;
            $prev        = $bracket['limit'];
        }

        return round($tax, 2);
    }

    /**
     * Đồng bộ các đơn nghỉ phép đã duyệt sang bảng attendances.
     */
    /**
     * Xác định hệ số nhân lương OT dựa trên ngày.
     */
    private function getOtMultiplier($date)
    {
        $dt = \Carbon\Carbon::parse($date);
        
        // 1. Kiểm tra ngày lễ (3.0x)
        if ($this->isHoliday($dt)) {
            return 3.0;
        }

        // 2. Kiểm tra cuối tuần (2.0x) - Giả định T7 và CN là ngày nghỉ
        if ($dt->isWeekend()) {
            return 2.0;
        }

        // 3. Ngày thường (1.5x)
        return 1.5;
    }

    /**
     * Kiểm tra ngày lễ Việt Nam (Cố định).
     */
    private function isHoliday($date)
    {
        $dayMonth = $date->format('d-m');
        $year = $date->year;

        // Danh sách ngày lễ dương lịch cố định
        $holidays = [
            '01-01', // Tết Dương lịch
            '30-04', // Giải phóng miền Nam
            '01-05', // Quốc tế lao động
            '02-09', // Quốc khánh
        ];

        if (in_array($dayMonth, $holidays)) {
            return true;
        }

        // Ghi chú: Với các ngày lễ âm lịch (Tết Nguyên Đán, Giỗ tổ Hùng Vương), 
        // trong thực tế cần một bảng database hoặc thư viện chuyển đổi âm dương.
        // Ở đây tạm thời tính các ngày lễ dương lịch chính.

        return false;
    }

    private function syncLeavesToAttendance($employeeId, $startDate, $endDate)
    {
        $approvedLeaves = \App\Models\LeaveRequest::where('employee_id', $employeeId)
            ->where('status', 'approved')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate]);
            })
            ->get();

        foreach ($approvedLeaves as $leave) {
            $period = \Carbon\CarbonPeriod::create(
                max($leave->start_date->toDateString(), $startDate),
                min($leave->end_date->toDateString(), $endDate)
            );

            foreach ($period as $date) {
                $dateStr = $date->toDateString();
                
                // Chỉ đồng bộ nếu chưa có chấm công hoặc đang là vắng mặt
                $existing = Attendance::where('employee_id', $employeeId)
                    ->where('date', $dateStr)
                    ->first();

                if (!$existing || $existing->status === 'absent') {
                    Attendance::updateOrCreate(
                        ['employee_id' => $employeeId, 'date' => $dateStr],
                        [
                            'status' => 'leave',
                            'notes' => ($leave->reason ?: 'Nghỉ phép đã duyệt') . ' (Mã đơn: #' . $leave->id . ')',
                            // Nếu nghỉ không lương thì actual_hours = 0
                            'actual_hours' => ($leave->type !== 'unpaid') ? 8 : 0,
                            'check_in_time' => $existing->check_in_time ?? null,
                            'check_out_time' => $existing->check_out_time ?? null,
                        ]
                    );
                }
            }
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(Salary $salary)
    {
        $salary->load('employee.position', 'employee.department');
        
        // Lấy danh sách OT trong kỳ để hiển thị đối soát
        $approvedOts = \App\Models\OvertimeRequest::where('employee_id', $salary->employee_id)
            ->where('status', 'approved')
            ->whereBetween('date', [$salary->pay_period_start, $salary->pay_period_end])
            ->get();

        // Gắn hệ số cho từng đơn OT để hiển thị ở View
        $approvedOts->each(function($ot) {
            $ot->multiplier = $this->getOtMultiplier($ot->date);
            $ot->type_text = ($ot->multiplier == 3.0) ? "Ngày lễ" : (($ot->multiplier == 2.0) ? "Ngày nghỉ" : "Ngày thường");
        });

        return view('admin.salaries.show', compact('salary', 'approvedOts'));
    }

    /**
     * Show the form for editing the specified resource.
     * Cho phép sửa chi tiết lương trước khi thanh toán?
     */
    public function edit(Salary $salary)
    {
        if ($salary->status === 'paid') {
            return redirect()->route('admin.salaries.show', $salary)->with('error', 'Không thể sửa bảng lương đã thanh toán.');
        }
        $salary->load('employee');
        return view('admin.salaries.edit', compact('salary'));
    }

    /**
     * Update the specified resource in storage.
     * Chủ yếu dùng để cập nhật chi tiết lương hoặc đánh dấu đã thanh toán.
     */
    public function update(UpdateSalaryRequest $request, Salary $salary)
    {
        $validated = $request->validated(); // Lấy dữ liệu đã validate

        // Xử lý đánh dấu 'paid'
        if ($request->has('mark_as_paid')) {
            $salary->update([
                'status' => 'paid',
                'paid_date' => now()->toDateString(),
                'notes' => ($salary->notes ?? '') . "\nMarked paid: " . now(),
            ]);
            return redirect()->route('admin.salaries.show', $salary)->with('success', 'Đã đánh dấu thanh toán.');
        }

        // Xử lý hủy 'cancelled' (thêm nút bấm riêng trong view)
        if ($request->has('mark_as_cancelled')) {
            if ($salary->status === 'paid') {
                return redirect()->route('admin.salaries.show', $salary)->with('error', 'Không thể hủy lương đã thanh toán.');
            }
            $salary->update([
                'status' => 'cancelled',
                'notes' => ($salary->notes ?? '') . "\nMarked cancelled: " . now(),
            ]);
            return redirect()->route('admin.salaries.index')->with('success', 'Đã hủy bỏ bảng lương.');
        }


        // Xử lý cập nhật chi tiết (nếu được phép)
        if (empty($validated)) { // Nếu request chỉ chứa mark_as_paid/cancelled đã xử lý ở trên
            return redirect()->route('admin.salaries.show', $salary);
        }

        // Tính lại net_salary dựa trên dữ liệu validated
        $validated['net_salary'] = max(
            0,
            ($validated['base_salary'] ?? $salary->base_salary) +
            ($validated['taxable_allowances'] ?? $salary->taxable_allowances) +
            ($validated['nontaxable_allowances'] ?? $salary->nontaxable_allowances) -
            ($validated['deductions'] ?? $salary->deductions) +
            ($validated['bonus'] ?? $salary->bonus) -
            ($validated['fines'] ?? $salary->fines)
        );
        $validated['notes'] = ($validated['notes'] ?? $salary->notes) . "\nAdmin updated: " . now();

        $salary->update($validated);

        return redirect()->route('admin.salaries.show', $salary)->with('success', 'Cập nhật chi tiết lương thành công.');
    }


    /**
     * Remove the specified resource from storage.
     * Có nên cho xóa record lương không? Hay chỉ nên cancel?
     */
    public function destroy(Salary $salary)
    {
        if ($salary->status === 'paid') {
            return redirect()->route('admin.salaries.index')->with('error', 'Không thể xóa bảng lương đã thanh toán.');
        }

        $salary->delete();
        return redirect()->route('admin.salaries.index')->with('success', 'Xóa bảng lương thành công.');
    }

    /**
     * Xuất phiếu lương dạng PDF.
     */
    public function exportPdf(Salary $salary)
    {
        $salary->load(['employee.department', 'employee.position']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.payslip', compact('salary'));
        return $pdf->download('payslip_' . $salary->employee->employee_code . '_' . $salary->month . '_' . $salary->year . '.pdf');
    }

    /**
     * Gửi phiếu lương qua email.
     */
    public function sendEmail(Salary $salary)
    {
        $salary->load(['employee.user', 'employee.department', 'employee.position']);
        
        if (!$salary->employee->user || !$salary->employee->user->email) {
            return redirect()->back()->with('error', 'Nhân viên này không có địa chỉ email hoặc chưa có tài khoản người dùng.');
        }

        try {
            \Illuminate\Support\Facades\Mail::to($salary->employee->user->email)->send(new \App\Mail\PayslipMail($salary));
            return redirect()->back()->with('success', 'Đã gửi email phiếu lương tới ' . $salary->employee->user->email);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Lỗi gửi mail lương: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Lỗi khi gửi email: ' . $e->getMessage());
        }
    }

    /**
     * Xuất báo cáo lương Excel theo tháng/năm.
     */
    public function exportExcel(\Illuminate\Http\Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\SalariesExport($month, $year), 
            "Bao_cao_luong_{$month}_{$year}.xlsx"
        );
    }

}