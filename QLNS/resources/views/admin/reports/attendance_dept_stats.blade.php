<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Báo Cáo Chuyên Cần Theo Phòng Ban') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    {{-- Form Lọc --}}
                    <form method="GET" action="{{ route('admin.reports.attendance-department') }}" class="mb-8">
                        <div class="flex items-end gap-4">
                            <div>
                                <x-input-label for="month" value="Tháng" />
                                <select name="month" id="month" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected($month == $m)>Tháng {{ $m }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <x-input-label for="year" value="Năm" />
                                <x-text-input type="number" name="year" id="year" class="block w-full mt-1" :value="$year" />
                            </div>
                            <x-primary-button>Xem báo cáo</x-primary-button>
                        </div>
                    </form>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        {{-- Bảng dữ liệu --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phòng ban</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Đi trễ (%)</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vắng mặt</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Tỷ lệ vắng (%)</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($stats as $row)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $row->name }}
                                            <div class="text-xs text-gray-400 font-normal">({{ $row->total_employees }} nhân viên)</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="text-sm text-amber-600 font-semibold">{{ $row->late_days }}</span>
                                            <span class="text-xs text-gray-400">({{ $row->late_rate }}%)</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-red-600 font-semibold">{{ $row->absent_days }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <div class="flex items-center justify-end">
                                                <span class="text-sm font-medium text-gray-900 mr-2">{{ $row->absent_rate }}%</span>
                                                <div class="w-24 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-red-600 h-2 rounded-full" style="width: {{ min($row->absent_rate, 100) }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Tóm tắt & Chú thích --}}
                        <div class="bg-indigo-50 p-6 rounded-lg border border-indigo-100">
                            <h3 class="text-indigo-900 font-bold mb-4">Giải thích cách tính</h3>
                            <div class="mb-4 p-3 bg-white rounded border border-indigo-200 text-sm">
                                <p class="font-bold text-indigo-700">Ngày công chuẩn tháng {{ $month }}/{{ $year }}: <span class="text-lg">{{ $standardDaysInMonth }} ngày</span></p>
                                <p class="text-xs text-gray-500">(Chỉ tính các ngày từ Thứ 2 đến Thứ 6 trong tháng)</p>
                            </div>
                            <ul class="space-y-3 text-sm text-indigo-800">
                                <li class="flex gap-2">
                                    <span class="font-bold text-indigo-600">•</span>
                                    <span><strong>Tỷ lệ đi trễ:</strong> = (Số lần đi trễ / Tổng số lần có mặt) x 100.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="font-bold text-indigo-600">•</span>
                                    <span><strong>Tỷ lệ vắng mặt:</strong> = (Tổng ngày vắng / (Số NV x {{ $standardDaysInMonth }} ngày)) x 100.</span>
                                </li>
                                <li class="flex gap-2">
                                    <span class="font-bold text-indigo-600">•</span>
                                    <span>Báo cáo này giúp đánh giá kỷ luật làm việc mà không bị phụ thuộc vào số lượng nhân viên khác nhau giữa các phòng ban.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
