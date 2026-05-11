<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Employee; // Import Employee
use Carbon\Carbon; // Import Carbon

class StoreEmployeeScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin(); // Chỉ admin được gán lịch
    }

    public function rules(): array
    {
        $employee = $this->route('employee'); // Lấy employee từ route

        return [
            'work_schedule_id' => 'required|exists:work_schedules,id',
            'start_date' => [
                'required',
                'date',
                // TODO: Thêm validation phức tạp hơn để kiểm tra overlap nếu cần
                // Ví dụ: đảm bảo start_date không nằm trong khoảng đã có lịch khác
            ],
            'end_date' => [
                'nullable', // Cho phép null nếu là lịch đang áp dụng
                'date',
                'after_or_equal:start_date' // Ngày kết thúc phải sau hoặc bằng ngày bắt đầu
            ],
        ];
    }

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