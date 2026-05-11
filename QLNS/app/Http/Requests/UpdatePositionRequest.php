<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePositionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Chỉ admin mới được cập nhật chức vụ
        return $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Lấy position từ route model binding
        $position = $this->route('position');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('positions')->ignore($position->id) // Bỏ qua chính nó khi check unique
            ],
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên chức vụ là bắt buộc.',
            'name.unique' => 'Tên chức vụ này đã tồn tại.',
        ];
    }
}