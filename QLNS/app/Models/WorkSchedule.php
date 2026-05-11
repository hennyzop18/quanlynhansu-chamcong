<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon; // Import Carbon để xử lý time

class WorkSchedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'work_schedules'; // Tên bảng trong database

    /**
     * The attributes that are mass assignable.
     * Các thuộc tính có thể gán hàng loạt.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'late_threshold',
        'break_hours',  // DESIGN#10: số giờ nghỉ giữa ca
    ];

    /**
     * The attributes that should be cast.
     * Các thuộc tính nên được ép kiểu.
     * Lưu ý: Ép kiểu TIME trực tiếp có thể không cần thiết,
     * Laravel thường xử lý tốt. Nhưng nếu cần định dạng cụ thể, có thể dùng.
     *
     * @var array<string, string>
     */
    // protected $casts = [
    //     'start_time' => 'datetime:H:i:s', // Hoặc để mặc định
    //     'end_time' => 'datetime:H:i:s',
    //     'late_threshold' => 'datetime:H:i:s',
    // ];

    /*
    |--------------------------------------------------------------------------
    | Relationships (Quan hệ)
    |--------------------------------------------------------------------------
    */

    /**
     * Lấy danh sách nhân viên được gán lịch làm việc này (quan hệ Many-to-Many).
     * Cần tạo bảng trung gian (pivot table) ví dụ: 'employee_work_schedule'
     * với các cột `employee_id` và `work_schedule_id`.
     *
     * public function employees()
     * {
     *     return $this->belongsToMany(Employee::class, 'employee_work_schedule')
     *                 ->withTimestamps(); // Nếu bảng pivot có timestamps
     * }
     */

    /*
    |--------------------------------------------------------------------------
    | Accessors & Mutators (Truy cập & Thiết lập) - Tùy chọn
    |--------------------------------------------------------------------------
    */

    /**
     * Lấy giờ bắt đầu dưới dạng đối tượng Carbon.
     *
     * @param  string|null  $value
     * @return \Carbon\Carbon|null
     */
    // public function getStartTimeAttribute($value): ?Carbon
    // {
    //     return $value ? Carbon::parse($value) : null;
    // }

    /**
     * Lấy giờ kết thúc dưới dạng đối tượng Carbon.
     *
     * @param  string|null  $value
     * @return \Carbon\Carbon|null
     */
    // public function getEndTimeAttribute($value): ?Carbon
    // {
    //     return $value ? Carbon::parse($value) : null;
    // }

    /**
     * Lấy mốc đi trễ dưới dạng đối tượng Carbon.
     *
     * @param  string|null  $value
     * @return \Carbon\Carbon|null
     */
    // public function getLateThresholdAttribute($value): ?Carbon
    // {
    //     return $value ? Carbon::parse($value) : null;
    // }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Phạm vi truy vấn) - Tùy chọn
    |--------------------------------------------------------------------------
    */

    /**
     * Lọc các ca làm việc hành chính (ví dụ).
     *
     * public function scopeAdministrative($query)
     * {
     *     return $query->where('name', 'like', '%Hành chính%');
     * }
     */

    /**
     * KIỂM TRA VẮNG MẶT / ĐẾN QUÁ MUỘN (SHOULD MARK ABSENT)
     *
     * Đây KHÔNG phải là "người không đến làm" — đó là trường hợp người check-in
     * nhưng đã quá muộn đến mức hệ thống coi như vắng (sau late_threshold).
     *
     * Người thực sự vắng mặt = không có bản ghi check-in trong ngày (xử lý ở job batch).
     *
     * @param Carbon $checkInTime Thời điểm check-in thực tế
     * @return bool True nếu nên đánh dấu 'absent' do đến cực kỳ trễ
     */
    public function shouldMarkAbsent(Carbon $checkInTime): bool
    {
        // Nếu không cấu hình late_threshold → không áp dụng logic này
        if (!$this->late_threshold) {
            return false;
        }

        $thresholdTime = Carbon::parse($checkInTime->toDateString() . ' ' . $this->late_threshold);

        return $checkInTime->gt($thresholdTime);
    }

    /**
     * @deprecated Dùng shouldMarkAbsent() thay thế
     */
    public function isAbsent(Carbon $checkInTime): bool
    {
        return $this->shouldMarkAbsent($checkInTime);
    }

    /**
     * KIỂM TRA ĐI TRỄ (LATE)
     *
     * Logic:
     * - Nếu có late_threshold: trễ khi check-in trong khoảng (start_time, late_threshold]
     * - Nếu KHÔNG có late_threshold: mọi check-in sau start_time đều là trễ (BUG#1 fix)
     *
     * @param Carbon $checkInTime Thời điểm check-in thực tế
     * @return bool True nếu đi trễ
     */
    public function isLate(Carbon $checkInTime): bool
    {
        $startTime = Carbon::parse($checkInTime->toDateString() . ' ' . $this->start_time);

        // Chưa đến giờ bắt đầu hoặc đúng giờ → không trễ
        if (!$checkInTime->gt($startTime)) {
            return false;
        }

        // Có late_threshold → chỉ "late" nếu chưa vượt qua ngưỡng đó
        // (nếu đã vượt ngưỡng → shouldMarkAbsent() sẽ xử lý)
        if ($this->late_threshold) {
            $thresholdTime = Carbon::parse($checkInTime->toDateString() . ' ' . $this->late_threshold);
            return $checkInTime->lte($thresholdTime);
        }

        // Không có late_threshold → chỉ cần sau start_time là đã trễ (BUG#1 fix)
        return true;
    }

    /**
     * KIỂM TRA ĐÚNG GIỜ (PRESENT)
     * Check-in sớm hơn hoặc đúng giờ start_time (cho phép đến trước 15 phút).
     *
     * @param Carbon $checkInTime
     * @return bool
     */
    public function isPresent(Carbon $checkInTime): bool
    {
        $startTime = Carbon::parse($checkInTime->toDateString() . ' ' . $this->start_time);
        $earlyMargin = $startTime->copy()->subMinutes(15); // Cho phép đến sớm tối đa 15 phút

        return $checkInTime->between($earlyMargin, $startTime);
    }

    public function isLeftEarly(Carbon $checkOutTime): bool
    {
        $endTimeToday = Carbon::parse($checkOutTime->toDateString() . ' ' . $this->end_time);
        
        // Tạo các mốc end_time để tìm mốc gần với check_out_time nhất
        $endTimeYesterday = $endTimeToday->copy()->subDay();
        $endTimeTomorrow = $endTimeToday->copy()->addDay();

        $diffToday = abs($checkOutTime->diffInSeconds($endTimeToday));
        $diffYesterday = abs($checkOutTime->diffInSeconds($endTimeYesterday));
        $diffTomorrow = abs($checkOutTime->diffInSeconds($endTimeTomorrow));

        // Tìm mốc end_time gần nhất
        $closestEndTime = $endTimeToday;
        if ($diffYesterday < $diffToday && $diffYesterday < $diffTomorrow) {
            $closestEndTime = $endTimeYesterday;
        } elseif ($diffTomorrow < $diffToday && $diffTomorrow < $diffYesterday) {
            $closestEndTime = $endTimeTomorrow;
        }

        // Về sớm nếu checkout trước mốc end_time gần nhất
        return $checkOutTime->lt($closestEndTime);
    }

    /**
     * Accessor: Tính số giờ làm việc thuần của ca (trừ giờ nghỉ).
     * Gọi qua: $schedule->duration_in_hours
     */
    public function getDurationInHoursAttribute(): float
    {
        $startTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        // Xử lý ca qua đêm
        if ($endTime->lt($startTime)) {
            $endTime->addDay();
        }

        $totalMinutes = $startTime->diffInMinutes($endTime, true);
        $totalHours = $totalMinutes / 60;

        // DESIGN#10 fix: Dùng cột break_hours thay vì brittle string matching
        // Nếu có break_hours trong DB → dùng đó; Fallback: tên ca chứa "hành chính" → 1 giờ
        $breakHours = $this->break_hours ?? 0;
        if ($breakHours === 0 && str_contains(strtolower($this->name ?? ''), 'hành chính')) {
            $breakHours = 1; // Legacy fallback, nên set break_hours trong DB
        }

        return ($totalHours > $breakHours) ? $totalHours - $breakHours : $totalHours;
    }
}