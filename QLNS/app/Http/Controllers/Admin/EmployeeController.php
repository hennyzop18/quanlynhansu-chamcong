<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Position;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\Department; // Thêm Department model
use App\Models\User; // Thêm User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // Thêm Hash
use Illuminate\Support\Str; // Thêm Str
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule; // Thêm Rule
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách nhân viên, có phân trang và tìm kiếm đơn giản
        $search = $request->input('search');
        $employees = Employee::with('position') // Eager load position
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('employee_code', 'like', "%{$search}%");
                });
            })
            ->latest() // Sắp xếp mới nhất lên đầu
            ->paginate(15); // Phân trang

        return view('admin.employees.index', compact('employees', 'search'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get(); // Lấy danh sách phòng ban
        $positions = Position::orderBy('name')->get(); // Lấy danh sách chức vụ
        return view('admin.employees.create', compact('positions', 'departments'));
    }

    public function store(Request $request)
    {
        // --- Validation ---
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:users,email',
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'dob' => 'nullable|date|before:today',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'hire_date' => 'required|date',
            'position_id' => 'required|exists:positions,id',
            'department_id' => 'required|exists:departments,id',
            'salary_type' => 'required|in:fixed,hourly',
            'base_salary' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'taxable_allowances' => 'nullable|numeric|min:0',
            'nontaxable_allowances' => 'nullable|numeric|min:0',
            'insurance_salary' => 'nullable|numeric|min:0',
            'dependents' => 'nullable|integer|min:0',
            'create_account' => 'nullable|boolean'
        ]);

        // --- Tạo Employee ---
        $employee = Employee::create([
            'first_name' => $validatedData['first_name'],
            'last_name' => $validatedData['last_name'],
            'gender' => $validatedData['gender'],
            'dob' => $validatedData['dob'],
            'phone_number' => $validatedData['phone_number'],
            'address' => $validatedData['address'],
            'hire_date' => $validatedData['hire_date'],
            'position_id' => $validatedData['position_id'],
            'department_id' => $validatedData['department_id'] ?? null,
            'status' => 'active', // Mặc định là active
            
            // Cấu hình lương
            'salary_type'           => $validatedData['salary_type'],
            'base_salary'           => $validatedData['base_salary'] ?? 0,
            'hourly_rate'           => $validatedData['hourly_rate'] ?? null,
            'taxable_allowances'    => $validatedData['taxable_allowances'] ?? 0,
            'nontaxable_allowances' => $validatedData['nontaxable_allowances'] ?? 0,
            'insurance_salary'      => $validatedData['insurance_salary'] ?? null,
            'dependents'            => $validatedData['dependents'] ?? 0,
        ]);

        // --- (Tùy chọn) Tạo User Account ---
        if ($request->boolean('create_account')) {
            $user = User::create([
                'name' => $employee->full_name, // Lấy tên đầy đủ từ employee
                'email' => $validatedData['email'],
                'password' => Hash::make(Str::random(10)), // Tạo mật khẩu ngẫu nhiên
                'role' => 'employee',
                'employee_id' => $employee->id, // Gán employee_id
                'email_verified_at' => now(), // Tự động xác thực email (hoặc gửi email xác thực)
            ]);

            // Gán user_id ngược lại vào employee nếu cần (tùy thiết kế DB)
            // $employee->user_id = $user->id;
            // $employee->save();

            // TODO: Gửi email thông báo tài khoản và mật khẩu cho nhân viên
        }


        return redirect()->route('admin.employees.index')->with('success', 'Thêm nhân viên thành công!');
    }

    public function show(Employee $employee)
    {
        // Eager load các quan hệ cần thiết
        $employee->load([
            'position',
            'user',
            'attendances' => function ($query) {
                $query->orderBy('date', 'desc')->limit(10); // Lấy 10 lần chấm công gần nhất
            }
        ]);
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::orderBy('name')->get(); // Lấy danh sách phòng ban
        $positions = Position::orderBy('name')->get();
        $employee->load('user'); // Load thông tin user nếu có để hiển thị email đăng nhập
        return view('admin.employees.edit', compact('employee', 'positions', 'departments'));
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee)
    {
        $validatedData = $request->validated();
        // Bắt đầu Transaction để đảm bảo cả Employee và User (nếu có) đều được cập nhật thành công
        DB::beginTransaction();

        try {
            // --- Cập nhật thông tin Employee ---
            // Loại bỏ 'email' khỏi $validatedData trước khi cập nhật Employee,
            // vì 'email' thuộc về User, không phải Employee.
            $employeeData = collect($validatedData)->except('email')->toArray();
            $employee->update($employeeData);

            // --- Cập nhật User liên kết (nếu có) ---
            if ($employee->user) { // Chỉ thực hiện nếu có User liên kết
                $userDataToUpdate = [];

                // Cập nhật email nếu được cung cấp trong request
                if ($request->filled('email')) {
                    $userDataToUpdate['email'] = $validatedData['email'];
                }

                // Cập nhật tên User nếu muốn đồng bộ (tùy chọn)
                // $userDataToUpdate['name'] = $employee->full_name;

                // **** LOGIC CẬP NHẬT TRẠNG THÁI USER ****
                // Nếu trạng thái Employee là 'active', kích hoạt User
                if ($validatedData['status'] === 'active') {
                    $userDataToUpdate['is_active'] = true;
                }
                // Nếu trạng thái Employee là 'inactive' hoặc 'terminated', vô hiệu hóa User
                elseif (in_array($validatedData['status'], ['inactive', 'terminated'])) {
                    $userDataToUpdate['is_active'] = false;
                }
                // **** KẾT THÚC LOGIC CẬP NHẬT TRẠNG THÁI USER ****

                // Chỉ gọi update User nếu có dữ liệu cần thay đổi
                if (!empty($userDataToUpdate)) {
                    $employee->user->update($userDataToUpdate);
                }
            }
            DB::commit();

            return redirect()->route('admin.employees.index')->with('success', 'Cập nhật thông tin nhân viên thành công!');

        } catch (\Exception $e) {
            // Rollback Transaction nếu có lỗi xảy ra
            DB::rollBack();

            // Log lỗi để debug
            Log::error('Error updating employee: ' . $e->getMessage());

            // Redirect về form edit với thông báo lỗi chung
            // return redirect()->back()->with('error', 'Đã xảy ra lỗi trong quá trình cập nhật. Vui lòng thử lại.');
            // Hoặc tốt hơn là trả về lỗi validation nếu có thể xác định
            return redirect()->back()
                ->withInput() // Giữ lại dữ liệu cũ trên form
                ->with('error', 'Đã xảy ra lỗi: ' . $e->getMessage()); // Hiển thị lỗi cụ thể (cẩn thận với thông tin nhạy cảm)
        }

    }

    public function destroy(Employee $employee)
    {
        // Đánh dấu nhân viên là inactive
        $employee->update(['status' => 'inactive']);

        // Nếu nhân viên có tài khoản user, cập nhật trạng thái user thành không hoạt động
        if ($employee->user) {
            $employee->user->update(['is_active' => false]);
        }

        return redirect()->route('admin.employees.index')->with('success', 'Xóa nhân viên thành công!');
    }
}