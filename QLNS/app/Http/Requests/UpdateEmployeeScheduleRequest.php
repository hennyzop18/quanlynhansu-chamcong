<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdateEmployeeScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $employee = $this->route('employee');
        $scheduleAssignment = $this->route('schedule_assignment'); // Lấy bản ghi đang sửa

        return [
            'work_schedule_id' => 'required|exists:work_schedules,id',
            'start_date' => [
                'required',
                'date',
                // TODO: Validation overlap khi update phức tạp hơn
                // Cần kiểm tra xem khoảng thời gian mới có chồng lấn
                // với các khoảng thời gian *khác* (ngoại trừ chính nó) không.
            ],
            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date'
            ],
        ];
    }

    // Có thể dùng lại messages từ Store
    public function messages(): array
    {
        return [
            'work_schedule_id.required' => 'Vui lòng chọn lịch làm việc.',
            'start_date.required' => 'Ngày bắt đầu áp dụng là bắt buộc.',
            'start_date.date' => 'Ngày bắt đầu không hợp lệ.',
            'end_date.date' => 'Ngày kết thúc không hợp lệ.',
            'end_date.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
        ];
    }
}