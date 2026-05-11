<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; // Import Carbon nếu bạn dùng Accessor/Mutator cho date

class EmployeeSchedule extends Model
{
    use HasFactory;

    /**
     * Tên bảng được liên kết với model.
     * Khai báo rõ ràng vì tên model không phải số nhiều của tên bảng.
     * @var string
     */
    protected $table = 'employee_schedules';

    /**
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'work_schedule_id',
        'start_date',
        'end_date',
    ];

    /**
     * Các thuộc tính nên được ép kiểu.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date', // Ép kiểu sang đối tượng Carbon date (YYYY-MM-DD)
        'end_date' => 'date',   // Tương tự, xử lý cả giá trị null
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Lấy thông tin nhân viên mà lịch này được gán.
     * (Quan hệ ngược của Employee -> hasMany EmployeeSchedule)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Lấy thông tin lịch làm việc (WorkSchedule) được gán.
     */
    public function workSchedule()
    {
        return $this->belongsTo(WorkSchedule::class);
    }
}