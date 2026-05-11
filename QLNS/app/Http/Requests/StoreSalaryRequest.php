<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalaryRequest extends FormRequest
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
            'month'               => 'required|integer|between:1,12',
            'year'                => 'required|integer|min:2000|max:' . (now()->year + 1),
            // Số ngày công chuẩn — admin có thể điều chỉnh (vd: tháng Tết ít ngày hơn)
            'standard_work_days'  => 'nullable|integer|min:1|max:31',
            // Lương ghi đè đồng loạt (chế độ override) — nullable = không dùng override
            'override_salary'     => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'month.required'              => 'Vui lòng chọn tháng.',
            'month.between'               => 'Tháng không hợp lệ.',
            'year.required'               => 'Vui lòng nhập năm.',
            'year.min'                    => 'Năm không hợp lệ.',
            'year.max'                    => 'Năm không hợp lệ.',
            'standard_work_days.min'      => 'Số ngày công chuẩn phải ít nhất là 1.',
            'standard_work_days.max'      => 'Số ngày công chuẩn không thể vượt quá 31.',
            'override_salary.min'         => 'Mức lương ghi đè phải là số dương.',
        ];
    }
}