<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Nếu cho đổi mật khẩu
use Illuminate\Validation\Rule; // Để validate email unique bỏ qua chính mình
use Illuminate\Validation\Rules\Password; // Nếu cho đổi mật khẩu
use App\Http\Requests\UpdateEmployeeProfileRequest;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the employee's own profile.
     */
    public function edit()
    {
        $user = Auth::user();
        $employee = $user->employee; // Lấy employee relationship

        if (!$employee) {
            Auth::logout();
            return redirect('/login')->with('error', 'Không tìm thấy thông tin nhân viên.');
        }
        return view('profile.edit', compact('employee', 'user'));
    }

    /**
     * Update the employee's own profile information.
     */
    public function update(UpdateEmployeeProfileRequest $request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        $validated = $request->validated(); // Lấy tất cả dữ liệu đã validate

        // Tách dữ liệu cho Employee và User
        $employeeData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'phone_number' => $validated['phone_number'] ?? null,
            'personal_email' => $validated['personal_email'] ?? null,
            'address' => $validated['address'] ?? null,
        ];

        $userData = [
            'name' => $validated['last_name'] . ' ' . $validated['first_name'],
            'email' => $validated['email'],
        ];

        // Cập nhật mật khẩu nếu có
        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        // Cập nhật Employee (chỉ cập nhật nếu có dữ liệu thay đổi)
        if (count(array_filter($employeeData)) > 0) { // Kiểm tra xem có giá trị nào khác null không
            $employee->update($employeeData);
        }

        // Cập nhật User
        $user->update($userData);

        // Đặt session status để view hiển thị thông báo "Đã lưu."
        return redirect()->route('employee.profile.edit')->with('status', 'profile-updated');
    }
    public function showFaceRegistrationForm()
    {
        return view('profile.face_registration');
    }
}