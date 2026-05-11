<x-admin-layout> {{-- Sử dụng layout admin của bạn, ví dụ x-app-layout hoặc x-admin-layout --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Duyệt Lịch Đăng Ký Của Nhân Viên') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                {{-- Hiển thị thông báo thành công/lỗi --}}
                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif
                
                {{-- Form chọn nhân viên và tháng --}}
                <form method="GET" action="{{ route('admin.schedule_approvals.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    {{-- Dropdown chọn nhân viên --}}
                    <div>
                        <label for="employee_id" class="block font-medium text-sm text-gray-700">Chọn nhân viên:</label>
                        <select id="employee_id" name="employee_id" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="">-- Chọn một nhân viên --</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected($selectedEmployee && $selectedEmployee->id == $employee->id)>
                                    {{ $employee->full_name }} ({{ $employee->employee_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dropdown chọn tháng --}}
                    <div>
                        <label for="month_select" class="block font-medium text-sm text-gray-700">Tháng:</label>
                        <select id="month_select" name="month_select" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected($targetDate->month == $m)>
                                    Tháng {{ $m }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    
                    {{-- Dropdown chọn năm --}}
                    <div>
                        <label for="year_select" class="block font-medium text-sm text-gray-700">Năm:</label>
                        <select id="year_select" name="year_select" class="block w-full mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @php
                                $currentYear = now()->year;
                            @endphp
                            @for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {{-- Hiển thị 2 năm trước và 1 năm sau --}}
                                <option value="{{ $y }}" @selected($targetDate->year == $y)>
                                    {{ $y }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Nút xem lịch bị di chuyển ra ngoài để căn chỉnh --}}
                    <div>
                        <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 h-10">Xem Lịch</button>
                    </div>
                </form>

                <hr class="my-6">

                @if($selectedEmployee)
                    {{-- Form phê duyệt lịch --}}
                    <form method="POST" action="{{ route('admin.schedule_approvals.approve') }}">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $selectedEmployee->id }}">
                        <input type="hidden" name="month" value="{{ $targetDate->format('Y-m') }}">

                        {{-- Thông tin tổng giờ --}}
                        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                            <h3 class="font-semibold text-lg">Lịch đăng ký của: {{ $selectedEmployee->full_name }} - Tháng {{ $targetDate->format('m/Y') }}</h3>
                            <p>Tổng giờ đã đăng ký: <span class="font-bold text-green-600">{{ number_format(abs($totalRegisteredHours), 1) }}</span> giờ</p>
                        </div>

                        {{-- Bảng lịch tháng --}}
                        <div class="overflow-x-auto pb-4">
                            <div class="grid grid-cols-7 gap-1 text-center min-w-[850px]">
                            @foreach(['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'] as $day)
                                <div class="font-bold p-2 text-sm text-gray-600">{{ $day }}</div>
                            @endforeach

                            @php
                                $firstDayOfMonth = $targetDate->copy()->startOfMonth();
                                $dayOfWeekOfFirstDay = $firstDayOfMonth->dayOfWeekIso;
                            @endphp

                            {{-- Các ô trống đầu tháng --}}
                            @for ($i = 1; $i < $dayOfWeekOfFirstDay; $i++)
                                <div class="border rounded-md bg-gray-50 h-32"></div>
                            @endfor

                            {{-- Các ngày trong tháng --}}
                            @for ($day = 1; $day <= $targetDate->daysInMonth; $day++)
                                @php
                                    $currentDate = $targetDate->copy()->setDay($day);
                                    $currentDateString = $currentDate->format('Y-m-d');
                                    $registration = $registrations->get($currentDateString);
                                @endphp
                                <div class="border rounded-md p-2 flex flex-col justify-between min-h-[120px] {{ $currentDate->isWeekend() ? 'bg-indigo-50' : 'bg-white' }}">
                                    <div class="font-bold {{ $currentDate->isWeekend() ? 'text-indigo-600' : '' }}">{{ $day }}</div>
                                    
                                    {{-- Select Ca làm việc --}}
                                    <select name="schedules[{{ $currentDateString }}][work_schedule_id]" class="mt-1 w-full text-xs rounded-md border-gray-300 schedule-select">
                                        <option value="">-- Trống / Nghỉ --</option>
                                        @foreach($workSchedules as $schedule)
                                            <option value="{{ $schedule->id }}" @selected($registration && $registration->work_schedule_id == $schedule->id)>
                                                {{ $schedule->name }} ({{ rtrim(rtrim(number_format($schedule->duration_in_hours, 1), '0'), '.') }}h)
                                            </option>
                                        @endforeach
                                    </select>
                                    
                                    {{-- Select Trạng thái --}}
                                    <select name="schedules[{{ $currentDateString }}][status]" class="mt-1 w-full text-xs rounded-md border-gray-300 status-select">
                                        <option value="pending" @selected(!$registration || $registration->status == 'pending')>Chờ duyệt</option>
                                        <option value="approved" @selected($registration && $registration->status == 'approved')>Đã duyệt</option>
                                        <option value="rejected" @selected($registration && $registration->status == 'rejected')>Từ chối</option>
                                    </select>
                                </div>
                            @endfor
                        </div> {{-- min-w-850px --}}
                    </div> {{-- overflow-x-auto --}}

                    <div class="mt-8 flex flex-col sm:flex-row justify-center items-center space-y-3 sm:space-y-0 sm:space-x-4">
                        <button type="button" id="approveAllBtn" class="w-full sm:w-auto px-8 py-3 bg-indigo-600 text-white font-bold rounded-lg shadow-lg hover:bg-indigo-700">
                            ✔️ Duyệt Tất Cả
                        </button>
                        
                        <button type="submit" class="w-full sm:w-auto px-8 py-3 bg-green-600 text-white font-bold rounded-lg shadow-lg hover:bg-green-700">
                            Lưu Thay Đổi
                        </button>
                    </div>
                    </form>
                @else
                    <p class="text-center text-gray-500 mt-8">Vui lòng chọn một nhân viên và tháng để xem lịch đăng ký.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Script chỉ được push vào nếu có nhân viên được chọn --}}
    @if($selectedEmployee)
        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const approveAllButton = document.getElementById('approveAllBtn');
                
                if (approveAllButton) {
                    // Lấy tất cả các select box có class tương ứng
                    const scheduleSelects = document.querySelectorAll('.schedule-select');
                    const statusSelects = document.querySelectorAll('.status-select');

                    approveAllButton.addEventListener('click', function () {
                        if (confirm('Bạn có chắc chắn muốn phê duyệt tất cả các ngày đã đăng ký ca làm việc trong tháng này?')) {
                            statusSelects.forEach((select, index) => {
                                // Chỉ duyệt những ngày có chọn ca làm việc
                                if (scheduleSelects[index] && scheduleSelects[index].value !== '') {
                                    select.value = 'approved';
                                    
                                    // Thay đổi màu nền để Admin thấy sự thay đổi ngay lập tức
                                    select.classList.remove('bg-yellow-100', 'bg-red-100');
                                    select.classList.add('bg-green-100', 'border-green-400');
                                }
                            });
                            // Thông báo cho người dùng rằng họ vẫn cần lưu
                            alert('Đã cập nhật trạng thái trên giao diện. Vui lòng nhấn "Lưu Thay Đổi" để xác nhận.');
                        }
                    });
                }
            });
        </script>
        @endpush
    @endif
</x-admin-layout>