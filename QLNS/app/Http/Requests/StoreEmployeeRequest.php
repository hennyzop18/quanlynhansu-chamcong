<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    // Cho phép ai được thực hiện request này (ví dụ: chỉ admin)
    public function authorize(): bool
    {
        // return true; // Hoặc kiểm tra quyền admin
        return $this->user()->isAdmin();
    }

    // Các quy tắc validation
    public function rules(): array
    {
        return [
            'employee_code' => 'required|string|unique:employees,employee_code|max:50',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required_if:create_account,true|nullable|string|email|max:255|unique:users,email', // Email required nếu check tạo TK
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'dob' => 'nullable|date|before:today',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'hire_date' => 'required|date',
            'position_id' => [
                'required',
                Rule::exists('positions', 'id')->whereNull('deleted_at') // Chỉ cho phép tồn tại nếu chưa bị soft delete
            ],
            'department_id' => 'nullable|exists:departments,id',
            'create_account' => 'nullable|boolean',

            // Cấu hình lương
            'salary_type'           => ['required', Rule::in(['fixed', 'hourly'])],
            'base_salary'           => 'nullable|numeric|min:0',
            'hourly_rate'           => 'nullable|numeric|min:0',
            'taxable_allowances'    => 'nullable|numeric|min:0',
            'nontaxable_allowances' => 'nullable|numeric|min:0',
            'insurance_salary'      => 'nullable|numeric|min:0',
            'dependents'            => 'nullable|integer|min:0',
        ];
    }

    // Tùy chỉnh thông báo lỗi (tùy chọn)
    public function messages(): array
    {
        return [
            'employee_code.required' => 'Mã nhân viên là bắt buộc.',
            'employee_code.unique' => 'Mã nhân viên đã tồn tại.',
            'email.required_if' => 'Email là bắt buộc khi chọn tạo tài khoản.',
            'email.unique' => 'Email này đã được sử dụng.',
            // ... các message khác
        ];
    }
}