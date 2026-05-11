<?php
namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
    /**
     * Determine whether the user can view any models.
     * Ai có thể xem danh sách nhân viên?
     */
    public function viewAny(User $user): bool
    {
        // Chỉ admin có thể xem danh sách
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the model.
     * Ai có thể xem chi tiết một nhân viên?
     */
    public function view(User $user, Employee $employee): bool
    {
        // Admin có thể xem bất kỳ ai
        // Nhân viên có thể xem hồ sơ của chính mình (nếu cần trang profile riêng cho admin xem)
        return $user->isAdmin() || ($user->isEmployee() && $user->employee_id === $employee->id);
    }

    /**
     * Determine whether the user can create models.
     * Ai có thể tạo nhân viên mới?
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     * Ai có thể cập nhật thông tin nhân viên?
     */
    public function update(User $user, Employee $employee): bool
    {
        // Chỉ admin có thể cập nhật thông tin NV qua trang quản lý của admin
        // (Nhân viên tự cập nhật thông tin cá nhân qua form profile riêng, không qua policy này)
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     * Ai có thể xóa nhân viên?
     */
    public function delete(User $user, Employee $employee): bool
    {
        // Chỉ admin có thể xóa
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     * Ai có thể khôi phục nhân viên đã xóa mềm? (Nếu dùng SoftDeletes)
     */
    public function restore(User $user, Employee $employee): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Ai có thể xóa vĩnh viễn nhân viên? (Nếu dùng SoftDeletes)
     */
    public function forceDelete(User $user, Employee $employee): bool
    {
        // Thường hạn chế quyền này, chỉ super admin chẳng hạn
        return false; // Hoặc return $user->isSuperAdmin();
    }
}