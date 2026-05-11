<?php
namespace App\Policies;

use App\Models\Salary;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalaryPolicy
{
    // Cho phép admin làm mọi thứ với Salary
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null; // Để các method khác quyết định cho role khác (nếu có)
    }

    public function viewAny(User $user): bool
    {
        // Admin đã được phép bởi before()
        // Nhân viên có được xem danh sách lương không? Thường là không.
        return false;
    }

    public function view(User $user, Salary $salary): bool
    {
        // Admin đã được phép bởi before()
        // Nhân viên có được xem chi tiết lương của chính mình không? (Cần trang riêng)
        // return $user->isEmployee() && $user->employee_id === $salary->employee_id;
        return false; // Giả sử chỉ admin xem qua đây
    }

    public function create(User $user): bool
    {
        // Admin đã được phép bởi before()
        return false;
    }

    public function update(User $user, Salary $salary): bool
    {
        // Admin đã được phép bởi before(), nhưng có thể thêm điều kiện không sửa lương đã paid
        if ($salary->status === 'paid') {
            // return Response::deny('Không thể cập nhật lương đã thanh toán.');
            return false; // Không cho update nếu đã paid (trừ khi logic update xử lý riêng việc mark_as_paid)
        }
        return true; // Admin được sửa lương chưa paid
    }

    public function delete(User $user, Salary $salary): bool
    {
        // Admin đã được phép bởi before(), nhưng có thể thêm điều kiện
        if ($salary->status === 'paid') {
            // return Response::deny('Không thể xóa lương đã thanh toán.');
            return false;
        }
        return true;
    }

    // restore và forceDelete tương tự delete
}