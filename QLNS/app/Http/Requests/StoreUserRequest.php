<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Chỉ admin mới được tạo user qua giao diện này
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::in(['admin', 'employee'])],
            // Employee_id chỉ bắt buộc và tồn tại nếu role là 'employee'
            'employee_id' => [
                Rule::requiredIf($this->role === 'employee'),
                'nullable', // Cho phép null nếu role là admin
                'exists:employees,id'
            ],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Tên người dùng là bắt buộc.',
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Email không đúng định dạng.',
            'email.unique' => 'Email này đã được sử dụng.',
            'password.required' => 'Mật khẩu là bắt buộc.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'employee_id.required_if' => 'Vui lòng chọn nhân viên liên kết cho vai trò Employee.',
            'employee_id.exists' => 'Nhân viên được chọn không tồn tại.',
        ];
    }
}