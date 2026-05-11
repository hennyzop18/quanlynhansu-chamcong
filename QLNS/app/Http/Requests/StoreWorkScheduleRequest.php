<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:work_schedules,name',
            'start_time' => 'required|date_format:H:i', // Định dạng Giờ:Phút (vd: 08:00)
            'end_time' => 'required|date_format:H:i|after:start_time', // Giờ kết thúc phải sau giờ bắt đầu
            'late_threshold' => 'nullable|date_format:H:i|after_or_equal:start_time', // Mốc đi trễ (nếu có) phải sau hoặc bằng giờ bắt đầu
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Tên ca làm việc là bắt buộc.',
            'name.unique' => 'Tên ca làm việc đã tồn tại.',
            'start_time.required' => 'Giờ bắt đầu là bắt buộc.',
            'start_time.date_format' => 'Định dạng giờ bắt đầu không đúng (HH:MM).',
            'end_time.required' => 'Giờ kết thúc là bắt buộc.',
            'end_time.date_format' => 'Định dạng giờ kết thúc không đúng (HH:MM).',
            'end_time.after' => 'Giờ kết thúc phải sau giờ bắt đầu.',
            'late_threshold.date_format' => 'Định dạng mốc đi trễ không đúng (HH:MM).',
            'late_threshold.after_or_equal' => 'Mốc đi trễ phải sau hoặc bằng giờ bắt đầu.',
        ];
    }
}