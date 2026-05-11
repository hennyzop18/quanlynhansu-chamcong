<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardDiscipline extends Model
{
    use HasFactory;

    // Đặt tên bảng rõ ràng nếu tên class khác tên bảng số nhiều
    protected $table = 'rewards_discipline';

    protected $fillable = [
        'employee_id',
        'type',
        'date',
        'reason',
        'amount',
    ];

    /**
     * Các thuộc tính nên được ép kiểu.
     *
     * @var array
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Lấy thông tin nhân viên liên quan.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}