<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $userToUpdate = $this->route('user'); // Lấy user từ route model binding

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($userToUpdate->id) // Bỏ qua chính user này
            ],
            'password' => ['nullable', 'confirmed', Password::defaults()], // Mật khẩu không bắt buộc khi update
            'role' => ['required', Rule::in(['admin', 'employee'])],
            'employee_id' => [
                Rule::requiredIf($this->role === 'employee'),
                'nullable',
                'exists:employees,id',
                // Đảm bảo employee_id này chưa được gán cho user khác (ngoại trừ user hiện tại)
                Rule::unique('users', 'employee_id')->ignore($userToUpdate->id)->whereNull('deleted_at'), // Thêm whereNull nếu dùng soft delete cho User
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
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'role.required' => 'Vui lòng chọn vai trò.',
            'employee_id.required_if' => 'Vui lòng chọn nhân viên liên kết cho vai trò Employee.',
            'employee_id.exists' => 'Nhân viên được chọn không tồn tại.',
            'employee_id.unique' => 'Nhân viên này đã được liên kết với một tài khoản khác.',
        ];
    }
}