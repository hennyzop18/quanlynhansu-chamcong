<x-employee-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Đăng Ký Lịch Làm Việc') }}
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
                @if (session('warning'))
                    <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg">
                        {{ session('warning') }}
                    </div>
                @endif
                
                {{-- Form chọn tháng --}}
                <form method="GET" action="{{ route('employee.schedule.index') }}" class="mb-6 grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    
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
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Xem</button>
                </form>
                
                {{-- Form đăng ký lịch --}}
                <form method="POST" action="{{ route('employee.schedule.store') }}">
                    @csrf
                    <input type="hidden" name="month" value="{{ $targetDate->format('Y-m') }}">

                    {{-- Thông tin tổng giờ --}}
                    <div class="mb-6 p-4 border rounded-lg bg-gray-50 space-y-2">
                        <h3 class="font-semibold text-lg text-gray-800">Thống kê tháng {{ $targetDate->format('m/Y') }}</h3>
                        <div class="flex justify-between items-center">
                            <span>Tổng giờ đã đăng ký:</span>
                            <span class="font-bold text-lg {{ $totalRegisteredHours >= $minimumHours ? 'text-green-600' : 'text-red-600' }}">{{ number_format(abs($totalRegisteredHours), 1) }} giờ</span>
                        </div>
                         <div class="flex justify-between items-center text-sm text-gray-600">
                            <span>Chỉ tiêu / Tối thiểu:</span>
                            <span class="font-bold">{{ $targetHours }} / {{ $minimumHours }} giờ</span>
                        </div>
                        {{-- Progress Bar --}}
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            @php $progress = ($targetHours > 0) ? ($totalRegisteredHours / $targetHours) * 100 : 0; @endphp
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min($progress, 100) }}%"></div>
                        </div>
                    </div>

                    {{-- Bảng lịch tháng --}}
                    <div class="overflow-x-auto pb-4">
                        <div class="grid grid-cols-7 gap-1 text-center min-w-[700px]">
                        {{-- Tiêu đề các ngày trong tuần --}}
                        @foreach(['T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'CN'] as $day)
                            <div class="font-bold p-2 text-sm text-gray-600">{{ $day }}</div>
                        @endforeach

                        @php
                            $firstDayOfMonth = $targetDate->copy()->startOfMonth();
                            $lastDayOfMonth = $targetDate->copy()->endOfMonth();
                            // 1 = Monday, 7 = Sunday
                            $dayOfWeekOfFirstDay = $firstDayOfMonth->dayOfWeekIso;
                        @endphp

                        {{-- Các ô trống đầu tháng --}}
                        @for ($i = 1; $i < $dayOfWeekOfFirstDay; $i++)
                            <div class="border rounded-md bg-gray-50 h-24"></div>
                        @endfor

                        {{-- Các ngày trong tháng --}}
                        @for ($day = 1; $day <= $lastDayOfMonth->day; $day++)
                            @php
                                $currentDate = $targetDate->copy()->setDay($day);
                                $currentDateString = $currentDate->format('Y-m-d');
                                $isPast = $currentDate->isPast() && !$currentDate->isToday();
                                $registration = $registrations->get($currentDateString);
                                $isWeekend = $currentDate->isWeekend();
                            @endphp
                            <div class="border rounded-md p-2 h-24 flex flex-col justify-between {{ $isPast ? 'bg-gray-100 text-gray-400' : ($isWeekend ? 'bg-indigo-50' : 'bg-white') }}">
                                <div class="font-bold {{ $isWeekend ? 'text-indigo-600' : '' }}">{{ $day }}</div>
                                @if(!$isPast)
                                    <select name="schedules[{{ $currentDateString }}]" class="w-full text-xs rounded-md border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <option value="">-- Nghỉ --</option>
                                        @foreach($workSchedules as $schedule)
                                            {{-- <option value="{{ $schedule->id }}" @selected($registration && $registration->work_schedule_id == $schedule->id)>
                                                {{ $schedule->name }} ({{ rtrim(rtrim(number_format($schedule->duration_in_hours, 1), '0'), '.') }}h)
                                            </option> --}}
                                            <option value="{{ $schedule->id }}"  @selected($registration && $registration->work_schedule_id == $schedule->id)>
                                                {{-- Sử dụng number_format để hiển thị 1 số lẻ và loại bỏ số 0 thừa --}}
                                                {{ $schedule->name }} ({{ rtrim(rtrim(number_format($schedule->duration_in_hours, 1), '0'), '.') }}h)
                                            </option>
                                        @endforeach
                                    </select>
                                    @if($registration)
                                        <div class="text-xs mt-1 capitalize font-semibold 
                                            @if($registration->status == 'approved') text-green-600 @endif
                                            @if($registration->status == 'pending') text-yellow-600 @endif
                                            @if($registration->status == 'rejected') text-red-600 @endif">
                                            {{ $registration->status == 'approved' ? 'Đã duyệt' : ($registration->status == 'pending' ? 'Chờ duyệt' : ($registration->status == 'rejected' ? 'Từ chối' : 'N/A')) }}
                                        </div>
                                    @endif
                                @else
                                    {{-- Hiển thị lịch đã qua --}}
                                @endif
                            </div>
                        @endfor
                    </div> {{-- min-w-700px --}}
                </div> {{-- overflow-x-auto --}}

                <div class="mt-8 text-center">
                    <button type="submit" class="w-full sm:w-auto px-12 py-3 bg-green-600 text-white font-bold rounded-lg shadow-lg hover:bg-green-700 transition duration-150">
                        Lưu Đăng Ký
                    </button>
                </div>
                </form>
            </div>
        </div>
    </div>
</x-employee-layout>