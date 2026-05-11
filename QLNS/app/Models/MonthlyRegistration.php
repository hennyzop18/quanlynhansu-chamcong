<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonthlyRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'work_schedule_id',
        'date',
        'scheduled_hours',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'scheduled_hours' => 'decimal:2',
    ];

    public function employee() {
        return $this->belongsTo(Employee::class);
    }

    public function workSchedule() {
        return $this->belongsTo(WorkSchedule::class);
    }
}