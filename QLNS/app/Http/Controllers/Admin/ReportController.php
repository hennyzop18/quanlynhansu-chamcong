<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Position;
use App\Models\User;
use App\Models\Salary;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Hiển thị trang chính của báo cáo hoặc một báo cáo mặc định.
     */
    public function index()
    {
        // Có thể hiển thị menu các loại báo cáo ở đây
        return view('admin.reports.index');
    }

    /**
     * Báo cáo thống kê nhân sự theo phòng ban, chức vụ.
     */
    public function employeeStatistics(Request $request)
    {
        $statsByDepartment = Department::withCount('employees')
            ->orderBy('employees_count', 'desc')
            ->get();

        $statsByPosition = Position::withCount('employees')
            ->orderBy('employees_count', 'desc')
            ->get();

        $totalEmployees = Employee::where('status', 'active')->count(); // Chỉ đếm NV đang hoạt động

        return view('admin.reports.employee_statistics', compact('statsByDepartment', 'statsByPosition', 'totalEmployees'));
    }

    /**
     * Báo cáo chấm công theo tháng.
     */
    public function attendanceReport(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        $employeeId = $request->input('employee_id'); // Lọc theo nhân viên (tùy chọn)

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $query = Attendance::with('employee')
            ->whereBetween('date', [$startDate, $endDate])
            ->select(
                'employee_id',
                DB::raw('COUNT(*) as total_records'), // Tổng số record chấm công
                DB::raw('SUM(CASE WHEN status = "present" OR status = "late" THEN 1 ELSE 0 END) as present_days'), // Số ngày có mặt (đi làm + đi trễ)
                DB::raw('SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_days'), // Số ngày đi trễ
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_days'), // Số ngày vắng
                DB::raw('SUM(CASE WHEN status = "leave" THEN 1 ELSE 0 END) as leave_days') // Số ngày nghỉ phép (nếu có)
                // TODO: Tính tổng giờ làm, giờ OT... nếu cần
            )
            ->groupBy('employee_id');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        $attendanceSummary = $query->get()->keyBy('employee_id'); // Key bằng employee_id để dễ join

        // Lấy thông tin tất cả nhân viên để hiển thị cả những người không có record chấm công trong tháng
        $employeesQuery = Employee::select('id', 'first_name', 'last_name', 'employee_code')
            ->where('status', 'active'); // Hoặc bao gồm cả inactive tùy yêu cầu báo cáo
        if ($employeeId) {
            $employeesQuery->where('id', $employeeId);
        }
        $employees = $employeesQuery->get();

        // Kết hợp thông tin nhân viên và tóm tắt chấm công
        $reportData = $employees->map(function ($employee) use ($attendanceSummary) {
            $summary = $attendanceSummary->get($employee->id);
            return (object) [ // Trả về object để dễ truy cập trong view
                'employee_code' => $employee->employee_code,
                'full_name' => $employee->full_name,
                'present_days' => $summary->present_days ?? 0,
                'late_days' => $summary->late_days ?? 0,
                'absent_days' => $summary->absent_days ?? 0,
                'leave_days' => $summary->leave_days ?? 0,
            ];
        });


        $allEmployees = Employee::where('status', 'active')->orderBy('first_name')->get(); // Để lọc


        return view('admin.reports.attendance_report', compact('reportData', 'month', 'year', 'allEmployees', 'employeeId'));
    }
    public function salaryReport(Request $request)
    {
        // ... (Lấy các biến filter như cũ) ...
        $filterMonth = $request->input('month', now()->month);
        $filterYear = $request->input('year', now()->year);
        $filterDepartment = $request->input('department_id');
        $filterEmployee = $request->input('employee_id');

        $payPeriodStart = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth();
        $payPeriodEnd = $payPeriodStart->copy()->endOfMonth();

        // Query cơ sở: BẮT ĐẦU TỪ EMPLOYEE hoặc JOIN
        // Cách 1: Join salaries vào employees (Phù hợp khi sắp xếp theo employee)
        $salaryQuery = Salary::query() // Bắt đầu query từ Salary
            ->select('salaries.*') // Chọn tất cả cột từ salaries để tránh trùng tên cột (vd: id)
            ->join('employees', 'salaries.employee_id', '=', 'employees.id') // JOIN với bảng employees
            ->with([ // Vẫn dùng with để eager load relationship cho dễ truy cập trong view
                'employee' => function ($q) {
                    $q->select('id', 'first_name', 'last_name', 'employee_code', 'department_id')
                        ->withOnly('department:id,name');
                }
            ])
            ->whereYear('salaries.pay_period_start', $filterYear) // Chỉ định rõ bảng khi có join
            ->whereMonth('salaries.pay_period_start', $filterMonth); // Chỉ định rõ bảng

        // Áp dụng bộ lọc nhân viên (trên bảng salaries hoặc employees đều được)
        if ($filterEmployee) {
            $salaryQuery->where('salaries.employee_id', $filterEmployee);
        }

        // Áp dụng bộ lọc phòng ban (phải lọc trên bảng employees đã join)
        if ($filterDepartment) {
            $salaryQuery->where('employees.department_id', $filterDepartment);
            // Hoặc dùng whereHas nếu không join ngay từ đầu, nhưng sẽ khó sort
            // $salaryQuery->whereHas('employee', function ($q) use ($filterDepartment) {
            //     $q->where('department_id', $filterDepartment);
            // });
        }

        // Lấy dữ liệu chi tiết và SẮP XẾP theo cột của employees
        $salaries = $salaryQuery->orderBy('employees.last_name', 'asc') // Bây giờ sắp xếp được
            ->orderBy('employees.first_name', 'asc') // Thêm first_name để sắp xếp đầy đủ
            ->paginate(50)
            ->withQueryString();

        // Query tính tổng hợp (có thể giữ nguyên hoặc join tương tự nếu cần filter phòng ban phức tạp)
        $summaryQuery = Salary::whereYear('pay_period_start', $filterYear)
            ->whereMonth('pay_period_start', $filterMonth);

        if ($filterEmployee) {
            $summaryQuery->where('employee_id', $filterEmployee);
        }
        // Áp dụng filter phòng ban cho summary query
        if ($filterDepartment) {
            $summaryQuery->whereHas('employee', function ($q) use ($filterDepartment) {
                $q->where('department_id', $filterDepartment);
            });
        }

        $totalNetSalary = $summaryQuery->sum('net_salary');
        $totalNetSalary = $summaryQuery->sum('net_salary');
        $totalBaseSalary = $summaryQuery->sum('base_salary');
        $totalTaxableAllowances = $summaryQuery->sum('taxable_allowances');
        $totalNontaxableAllowances = $summaryQuery->sum('nontaxable_allowances');
        $totalAllowances = $totalTaxableAllowances + $totalNontaxableAllowances;
        $totalDeductions = $summaryQuery->sum('deductions');
        $totalBonus = $summaryQuery->sum('bonus');
        $totalFines = $summaryQuery->sum('fines');
        $employeeCount = $summaryQuery->distinct('employee_id')->count();


        // Lấy danh sách phòng ban và nhân viên để lọc (giữ nguyên)
        $departments = Department::orderBy('name')->select('id', 'name')->get();
        $employees = Employee::where('status', 'active')->orderBy('first_name')->select('id', 'first_name', 'last_name', 'employee_code')->get();


        return view('admin.reports.salary_report', compact(
            'salaries',
            'filterMonth',
            'filterYear',
            'filterDepartment',
            'filterEmployee',
            'departments',
            'employees',
            'totalNetSalary',
            'totalBaseSalary',
            'totalAllowances',
            'totalDeductions',
            'totalBonus',
            'totalFines',
            'employeeCount'
        ));
    }

    public function attendanceDepartmentStats(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // Tính số ngày làm việc chuẩn trong tháng này (loại bỏ Thứ 7, CN)
        $standardDaysInMonth = 0;
        $tempDate = $startDate->copy();
        while ($tempDate <= $endDate) {
            if (!$tempDate->isWeekend()) {
                $standardDaysInMonth++;
            }
            $tempDate->addDay();
        }

        $stats = Department::with(['employees' => function($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->map(function($dept) use ($startDate, $endDate, $standardDaysInMonth) {
                $employeeIds = $dept->employees->pluck('id');
                
                $attendance = Attendance::whereIn('employee_id', $employeeIds)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->selectRaw("
                        COUNT(*) as total_records,
                        SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_days,
                        SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_days
                    ")
                    ->first();

                $totalEmployees = $dept->employees->count();
                $totalExpectedDays = $totalEmployees * $standardDaysInMonth; 

                return (object) [
                    'name' => $dept->name,
                    'total_employees' => $totalEmployees,
                    'late_days' => $attendance->late_days ?? 0,
                    'absent_days' => $attendance->absent_days ?? 0,
                    'total_records' => $attendance->total_records ?? 0,
                    'late_rate' => ($attendance->total_records ?? 0) > 0 ? round(($attendance->late_days / $attendance->total_records) * 100, 2) : 0,
                    'absent_rate' => $totalExpectedDays > 0 ? round(($attendance->absent_days / $totalExpectedDays) * 100, 2) : 0,
                ];
            });

        return view('admin.reports.attendance_dept_stats', compact('stats', 'month', 'year', 'standardDaysInMonth'));
    }

}