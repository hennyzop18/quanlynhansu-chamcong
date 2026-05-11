<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRewardDisciplineRequest extends FormRequest
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
        return [
            'employee_id' => 'required|exists:employees,id',
            'type' => ['required', Rule::in(['reward', 'discipline'])],
            'date' => 'required|date|before_or_equal:today', // Ngày không được trong tương lai
            'reason' => 'required|string|max:1000', // Giới hạn độ dài lý do
            'amount' => 'nullable|numeric|min:0', // Cho phép null hoặc số không âm
        ];
    }
    public function messages(): array
    {
        return [
            'employee_id.required' => 'Vui lòng chọn nhân viên.',
            'employee_id.exists' => 'Nhân viên không tồn tại.',
            'type.required' => 'Vui lòng chọn loại (Khen thưởng/Kỷ luật).',
            'date.required' => 'Ngày ghi nhận là bắt buộc.',
            'date.before_or_equal' => 'Ngày ghi nhận không được trong tương lai.',
            'reason.required' => 'Lý do là bắt buộc.',
            'amount.numeric' => 'Số tiền phải là một số.',
            'amount.min' => 'Số tiền không được âm.',
        ];
    }
}