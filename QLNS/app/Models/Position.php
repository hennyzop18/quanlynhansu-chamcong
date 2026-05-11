<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Position extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'description'];

    /**
     * Các thuộc tính nên được "date" hóa.
     * SoftDeletes tự động xử lý 'deleted_at', nhưng thêm vào đây cũng không sao.
     * @var array
     */
    protected $dates = ['deleted_at'];

    // Quan hệ: Một Position có nhiều Employees
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}