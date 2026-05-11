<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateEmployeeProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Chỉ user đang đăng nhập mới được sửa profile của mình
     */
    public function authorize(): bool
    {
        // Đảm bảo user đã đăng nhập (middleware 'auth' đã xử lý)
        return true; // Hoặc return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $user = $this->user(); // Lấy user đang đăng nhập

        return [
            // --- Phần Employee ---
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'personal_email' => 'nullable|email|max:255',
            'address' => 'nullable|string|max:500',

            // --- Phần User ---
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id) // Bỏ qua chính user này
            ],
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Tên hiển thị là bắt buộc.',
            'email.required' => 'Email đăng nhập là bắt buộc.',
            'email.email' => 'Email đăng nhập không đúng định dạng.',
            'email.unique' => 'Email đăng nhập này đã được sử dụng.',
            'current_password.required_with' => 'Bạn phải nhập mật khẩu hiện tại để đổi mật khẩu mới.',
            'current_password.current_password' => 'Mật khẩu hiện tại không đúng.',
            'password.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ];
    }
}