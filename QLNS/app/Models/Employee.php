<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes
use App\Models\WorkSchedule;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory, SoftDeletes; // Sử dụng SoftDeletes

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($employee) {
            if (empty($employee->employee_code)) {
                // Tạo mã ngẫu nhiên QLNS-XXXXXX (6 ký tự)
                $employee->employee_code = 'QLNS-' . strtoupper(bin2hex(random_bytes(3)));
            }
        });
    }

    protected $fillable = [
        'employee_code',
        'first_name',
        'last_name',
        'gender',
        'dob',
        'phone_number',
        'personal_email',
        'address',
        'hire_date',
        'termination_date',
        'position_id',
        'work_schedule_id',
        'department_id',
        'status',
        'avatar',
        // Thêm cột lương từ migration 2026_04_12
        'base_salary',
        'taxable_allowances',
        'nontaxable_allowances',
        'insurance_salary',
        'dependents',
        'salary_type',
        'hourly_rate',
    ];

    protected $casts = [
        'dob'              => 'date',
        'hire_date'        => 'date',
        'termination_date' => 'date',
        'base_salary'           => 'decimal:2',
        'taxable_allowances'    => 'decimal:2',
        'nontaxable_allowances' => 'decimal:2',
        'insurance_salary'      => 'decimal:2',
        'hourly_rate'      => 'decimal:2',
        'dependents'       => 'integer',
    ];

    // Quan hệ: Một Employee thuộc về một Position
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);

    }
    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class);
    }
    public function getApprovedLeaveOn(Carbon $date): ?LeaveRequest
    {
        return $this->leaveRequests()
            ->where('status', 'approved')
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->first();
    }

    public function activeWorkScheduleOn(Carbon $date): ?WorkSchedule
    {
        $targetDate = $date->toDateString();

        // 1. Ưu tiên tìm trong lịch ĐĂNG KÝ HÀNG THÁNG đã được duyệt ('approved')
        $monthlyRegistration = $this->monthlyRegistrations()
            ->where('date', $targetDate)
            ->where('status', 'approved')
            ->with('workSchedule')
            ->first();
            
        if ($monthlyRegistration && $monthlyRegistration->workSchedule) {
            return $monthlyRegistration->workSchedule;
        }

        // 2. Nếu không có, tìm trong lịch GÁN DÀI HẠN (bảng employee_schedules)
        $assignedSchedule = $this->employeeSchedules()
            ->where('start_date', '<=', $targetDate)
            ->where(function ($query) use ($targetDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $targetDate);
            })
            ->orderBy('start_date', 'desc')
            ->with('workSchedule')
            ->first();
            
        if ($assignedSchedule && $assignedSchedule->workSchedule) {
            return $assignedSchedule->workSchedule;
        }
        
        // 3. Nếu không có cả hai, trả về null
        return null;
    }

    // (Tùy chọn) Phương thức lấy lịch làm việc MẶC ĐỊNH hiện tại (để cập nhật cột employees.work_schedule_id)
    public function getCurrentOrDefaultWorkSchedule(): ?WorkSchedule
    {
        // 1. Ưu tiên lịch đang active hôm nay
        $todaySchedule = $this->activeWorkScheduleOn(Carbon::today());
        if ($todaySchedule) {
            return $todaySchedule;
        }

        // 2. Nếu không có, tìm lịch gần nhất trong tương lai
        $futureAssignment = $this->employeeSchedules()
            ->where('start_date', '>', Carbon::today()->toDateString())
            ->orderBy('start_date', 'asc')
            ->with('workSchedule')
            ->first();
        if ($futureAssignment) {
            return $futureAssignment->workSchedule;
        }

        // 3. Nếu không có cả tương lai, lấy lịch gần nhất trong quá khứ (nếu cần)
        $pastAssignment = $this->employeeSchedules()
            ->orderBy('start_date', 'desc') // Lấy cái mới nhất
            ->with('workSchedule')
            ->first();
        if ($pastAssignment) {
            return $pastAssignment->workSchedule;
        }


        // 4. Nếu không có lịch nào được gán, trả về null (hoặc lịch mặc định của hệ thống)
        // return WorkSchedule::find(DEFAULT_SYSTEM_SCHEDULE_ID);
        return null;
    }
    // Quan hệ: Một Employee có thể có một User account

    // Quan hệ: Một Employee có nhiều Attendance records
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
   
    // Accessor để lấy tên đầy đủ
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
    
    public function employeeSchedules() { // Lịch gán dài hạn
        return $this->hasMany(EmployeeSchedule::class);
    }
    public function monthlyRegistrations() { // Lịch đăng ký hàng tháng
        return $this->hasMany(MonthlyRegistration::class);
    }
    public function salaries() { // Bảng lương
        return $this->hasMany(Salary::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function overtimeRequests()
    {
        return $this->hasMany(OvertimeRequest::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'employee_id', 'id');
    }

}