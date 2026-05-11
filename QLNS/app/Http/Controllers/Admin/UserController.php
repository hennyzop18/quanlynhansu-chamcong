<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Employee; // Để liên kết hoặc hiển thị
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role   = $request->input('role');

        $query = User::with(['employee' => function($q) {
            $q->select('id', 'first_name', 'last_name', 'employee_code', 'avatar', 'personal_email', 'phone_number', 'department_id', 'position_id')
              ->with(['department:id,name', 'position:id,name']);
        }])->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        $users = $query->paginate(15);

        // Chuẩn bị dữ liệu cho Popup
        $users->each(function($user) {
            if ($user->employee) {
                $user->employee->avatar_url = $user->employee->avatar ? asset('storage/' . $user->employee->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->employee->full_name) . '&color=7F9CF5&background=EBF4FF';
            }
        });

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    /**
     * Show the form for creating a new resource.
     * Thường tạo user khi tạo employee, nhưng có thể tạo admin riêng.
     */
    public function create()
    {
         $employees = Employee::whereDoesntHave('user')
                        ->orderBy('last_name')
                        ->orderBy('first_name')
                        ->get();
         
        return view('admin.users.create', compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()], // Dùng Rule Password mặc định
            'role' => ['required', Rule::in(['admin', 'employee'])],
            'employee_id' => 'nullable|exists:employees,id', // Cho phép liên kết với Employee nếu role là employee
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'employee_id' => ($validated['role'] === 'employee') ? $validated['employee_id'] : null,
            'email_verified_at' => now(), // Tự động verify khi admin tạo
        ]);

        return redirect()->route('admin.users.index')->with('success', 'Tạo tài khoản người dùng thành công!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('employee');
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $employees = Employee::whereDoesntHave('user') // Chỉ hiện NV chưa có tài khoản để liên kết
            ->orWhere('id', $user->employee_id) // Hoặc NV đang liên kết với user này
            ->orderBy('first_name')
            ->get();
        return view('admin.users.edit', compact('user', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::defaults()], // Password không bắt buộc khi update
            'role' => ['required', Rule::in(['admin', 'employee'])],
            // Chỉ cho phép sửa employee_id nếu role là employee
            'employee_id' => $request->role === 'employee' ? 'nullable|exists:employees,id' : '',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            // Gán employee_id nếu role là employee, ngược lại set null
            'employee_id' => ($validated['role'] === 'employee') ? $request->input('employee_id') : null,
        ];

        // Chỉ cập nhật password nếu có nhập
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật tài khoản người dùng thành công!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Không nên cho xóa tài khoản admin cuối cùng
        if ($user->isAdmin() && User::where('role', 'admin')->count() <= 1) {
            return redirect()->route('admin.users.index')->with('error', 'Không thể xóa tài khoản Admin cuối cùng.');
        }
        $userauth = Auth::user();

        // Kiểm tra nếu là tài khoản của chính mình
        if ($user->id === $userauth->id) {
            return redirect()->route('admin.users.index')->with('error', 'Bạn không thể xóa chính mình.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Xóa tài khoản người dùng thành công!');
    }
}