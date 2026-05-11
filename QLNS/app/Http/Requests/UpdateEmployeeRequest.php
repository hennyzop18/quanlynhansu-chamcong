<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Xác định xem người dùng có được phép thực hiện yêu cầu này không.
     */
    public function authorize(): bool
    {
        // Chỉ admin mới được cập nhật thông tin nhân viên qua route này
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     * Lấy các quy tắc validation áp dụng cho yêu cầu.
     */
    public function rules(): array
    {
        // Lấy employee từ route model binding
        $employee = $this->route('employee');

        return [
            'employee_code' => ['sometimes', 'nullable', 'string', 'max:50', Rule::unique('employees')->ignore($employee->id)],
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            // Cho phép cập nhật email user nếu có user liên kết (Rule unique bỏ qua user hiện tại)
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee->user?->id)],
            'gender' => ['required', Rule::in(['male', 'female', 'other'])],
            'dob' => 'nullable|date|before:today',
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'hire_date' => 'required|date',
            'termination_date' => 'nullable|date|after_or_equal:hire_date',
            'position_id' => 'required|exists:positions,id',
            'department_id' => 'nullable|exists:departments,id', // Thêm department nếu có
            'status' => ['required', Rule::in(['active', 'inactive', 'terminated'])],
            
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
}