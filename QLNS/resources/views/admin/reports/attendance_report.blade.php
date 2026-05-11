<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Báo cáo Chấm Công Tháng') }} {{ $month }}/{{ $year }}
            </h2>
            <a href="{{ route('admin.reports.index') }}">
                <x-secondary-button>
                    ← {{ __('Danh sách Báo cáo') }}
                </x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8"> {{-- max-w-full cho bảng rộng --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Form Lọc --}}
                    <form method="GET" action="{{ route('admin.reports.attendance-report') }}"
                        class="mb-6 p-4 bg-gray-50 rounded-md border">
                        <p class="text-sm font-medium text-gray-700 mb-2">Lọc báo cáo:</p>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="month" value="Tháng" />
                                <select name="month" id="month"
                                    class="block w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected($month == $m)>
                                            Tháng {{ $m }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <x-input-label for="year" value="Năm" />
                                <x-text-input type="number" name="year" id="year" class="block w-full mt-1" min="2020"
                                    max="{{ now()->year + 1 }}" :value="$year" required />
                            </div>
                            <div>
                                <x-input-label for="employee_id" value="Nhân Viên (Tùy chọn)" />
                                <select name="employee_id" id="employee_id"
                                    class="block w-full mt-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="">-- Tất cả Nhân Viên --</option>
                                    @foreach($allEmployees as $employee) {{-- Biến này chứa tất cả NV để lọc --}}
                                        <option value="{{ $employee->id }}" @selected($employeeId == $employee->id)>
                                            {{ $employee->last_name }} {{ $employee->first_name }}
                                            ({{$employee->employee_code}})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                        </div>
                    </form>

                    {{-- Hiển thị thông báo thành công/lỗi nếu có --}}
                    <x-session-status class="mb-4" :status="session('success')" />
                    @if(session('error'))
                        <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded border border-red-200">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto mt-6">
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Mã NV</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Nhân Viên</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                        title="Số ngày có chấm công vào/ra (Present + Late)">Ngày Công</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                        title="Số ngày Check-in sau giờ quy định">Đi Trễ</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                        title="Số ngày được ghi nhận là Vắng Mặt (Absent)">Vắng Mặt</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider"
                                        title="Số ngày được ghi nhận là Nghỉ Phép (Leave)">Nghỉ Phép</th>
                                    {{-- Thêm các cột khác nếu cần: Giờ làm, OT,... --}}
                                    {{-- <th
                                        class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Tổng Giờ Làm</th> --}}
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($reportData as $data) {{-- $reportData từ controller --}}
                                    <tr>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                            {{ $data->employee_code }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $data->full_name }}
                                        </td>
                                        <td class="px-4 py-2 whitespace-nowrap text-sm text-center text-gray-700">
                                            {{ $data->present_days ?? 0 }}
                                        </td>
                                        <td
                                            class="px-4 py-2 whitespace-nowrap text-sm text-center {{ ($data->late_days ?? 0) > 0 ? 'text-yellow-600 font-semibold' : 'text-gray-700' }}">
                                            {{ $data->late_days ?? 0 }}
                                        </td>
                                        <td
                                            class="px-4 py-2 whitespace-nowrap text-sm text-center {{ ($data->absent_days ?? 0) > 0 ? 'text-red-600 font-semibold' : 'text-gray-700' }}">
                                            {{ $data->absent_days ?? 0 }}
                                        </td>
                                        <td
                                            class="px-4 py-2 whitespace-nowrap text-sm text-center {{ ($data->leave_days ?? 0) > 0 ? 'text-blue-600 font-semibold' : 'text-gray-700' }}">
                                            {{ $data->leave_days ?? 0 }}
                                        </td>
                                        {{-- <td class="px-4 py-2 whitespace-nowrap text-sm text-right text-gray-700">{{
                                            $data->total_hours ?? 'N/A' }}</td> --}}
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ liệu
                                            chấm công cho kỳ đã chọn.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            {{-- Có thể thêm dòng tổng cộng ở đây nếu cần --}}
                            {{-- <tfoot class="bg-gray-50 font-semibold">
                                <tr>
                                    <td colspan="2" class="px-4 py-2 text-right text-sm">Tổng cộng:</td>
                                    <td class="px-4 py-2 text-center text-sm">{{ $reportData->sum('present_days') }}
                                    </td>
                                    <td class="px-4 py-2 text-center text-sm">{{ $reportData->sum('late_days') }}</td>
                                    <td class="px-4 py-2 text-center text-sm">{{ $reportData->sum('absent_days') }}</td>
                                    <td class="px-4 py-2 text-center text-sm">{{ $reportData->sum('leave_days') }}</td>
                                    <td class="px-4 py-2 text-right text-sm"></td>
                                </tr>
                            </tfoot> --}}
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>