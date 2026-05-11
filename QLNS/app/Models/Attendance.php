<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'check_in_time',
        'check_out_time',
        'actual_hours',
        'date',
        'status',
        'notes',
    ];

    protected $casts = [
        'check_in_time'  => 'datetime',
        'check_out_time' => 'datetime',
        'date'           => 'date',
        'actual_hours'   => 'decimal:2',
    ];

    // Quan hệ: Một Attendance record thuộc về một Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}