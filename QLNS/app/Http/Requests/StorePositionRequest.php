<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:positions,name|max:255',
            'description' => 'nullable|string',
        ];
    }
    public function messages(): array // Tùy chỉnh thông báo lỗi (tùy chọn)
    {
        return [
            'name.required' => 'Tên chức vụ là bắt buộc.',
            'name.unique' => 'Tên chức vụ này đã tồn tại.',
        ];
    }
}