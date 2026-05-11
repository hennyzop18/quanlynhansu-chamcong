<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /**
     * Get the name of the password field for authentication.
     *
     * @return string
     */
    public function getAuthPasswordName(): string
    {
        return 'password';
    }
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // Thêm role
        'is_active', // Thêm is_active
        'employee_id', // Thêm employee_id
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    
    public function faceDescriptors()
    {
        // Giả sử mỗi user chỉ có 1 khuôn mặt đăng ký
        return $this->hasOne(FaceDescriptor::class, 'user_id', 'id');
    }

    

    // Quan hệ: Một User có thể là một Employee
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');// Hoặc hasOne nếu employee_id trên users
    }

    // Helper kiểm tra role
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
