<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSalaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Chỉ admin mới được sửa lương
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $salary = $this->route('salary');

        // Không cho sửa nếu đã thanh toán, trừ khi chỉ là request đánh dấu đã thanh toán
        if ($salary->status === 'paid' && !$this->has('mark_as_paid')) {
            return [
                // Trả về một rule luôn fail hoặc một thông báo lỗi cụ thể
                'cannot_edit_paid' => 'required' // Sẽ báo lỗi "The cannot edit paid field is required."
            ];
        }

        // Nếu chỉ là đánh dấu thanh toán, không cần validate gì thêm
        if ($this->has('mark_as_paid')) {
            return [];
        }

        // Validate các trường chi tiết lương
        return [
            'base_salary' => 'required|numeric|min:0',
            'taxable_allowances' => 'required|numeric|min:0',
            'nontaxable_allowances' => 'required|numeric|min:0',
            'ot_hours' => 'nullable|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'bonus' => 'required|numeric|min:0',
            'fines' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            // Không cho sửa employee_id, pay_period qua đây
        ];
    }

    public function messages(): array
    {
        return [
            'cannot_edit_paid.required' => 'Không thể chỉnh sửa bảng lương đã được thanh toán.',
            'base_salary.required' => 'Lương cơ bản là bắt buộc.',
            'base_salary.numeric' => 'Lương cơ bản phải là số.',
            'base_salary.min' => 'Lương cơ bản không được âm.',
            // Thêm các message khác cho allowances, deductions, bonus, fines
        ];
    }
}