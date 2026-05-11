<x-employee-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Giải trình Chấm công') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="mb-6 p-4 bg-gray-50 rounded border">
                        <p class="text-sm font-semibold text-gray-700">Thông tin chấm công gốc:</p>
                        <p class="text-sm text-gray-600">Ngày: {{ $attendance->date->format('d/m/Y') }}</p>
                        <p class="text-sm text-gray-600">Giờ: {{ $attendance->check_in_time?->format('H:i') ?? '--' }} - {{ $attendance->check_out_time?->format('H:i') ?? '--' }}</p>
                        <p class="text-sm text-gray-600">Trạng thái: <span class="text-red-600 font-bold uppercase">{{ $attendance->status }}</span></p>
                    </div>

                    <form method="POST" action="{{ route('employee.attendance.adjust.store', $attendance) }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="type" value="Loại giải trình" />
                            <select name="type" id="type" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                <option value="late">Đi trễ có lý do</option>
                                <option value="early">Về sớm có lý do</option>
                                <option value="forget">Quên chấm công (Harassment/Technical error)</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="reason" value="Lý do chi tiết" />
                            <textarea name="reason" id="reason" rows="4" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" placeholder="Nhập lý do giải trình..."></textarea>
                            <p class="mt-1 text-xs text-gray-500 italic">Ví dụ: Kẹt xe, hỏng xe, đi gặp khách hàng, lỗi hệ thống kiosk...</p>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('employee.attendance.history') }}">
                                <x-secondary-button type="button">Hủy</x-secondary-button>
                            </a>
                            <x-primary-button>Gửi giải trình</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-employee-layout>
