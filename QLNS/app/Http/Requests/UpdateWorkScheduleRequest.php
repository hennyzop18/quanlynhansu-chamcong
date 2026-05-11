<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $schedule = $this->route('work_schedule');
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('work_schedules')->ignore($schedule->id)],
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'late_threshold' => 'nullable|date_format:H:i|after_or_equal:start_time',
        ];
    }
    // Có thể dùng lại messages từ StoreWorkScheduleRequest
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