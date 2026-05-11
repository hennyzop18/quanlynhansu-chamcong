<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Department extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
    ];

    protected $dates = ['deleted_at'];
    /**
     * Lấy danh sách nhân viên thuộc phòng ban này.
     */
    public function employees()
    {
        // Giả định bạn đã thêm cột `department_id` vào bảng `employees`
        return $this->hasMany(Employee::class);
    }
}