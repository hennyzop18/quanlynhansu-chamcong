<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Báo cáo Tổng hợp Lương Tháng') }} {{ $filterMonth }}/{{ $filterYear }}
            </h2>
            <a href="{{ route('admin.reports.index') }}">
                <x-secondary-button>
                    ← {{ __('Danh sách Báo cáo') }}
                </x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8"> {{-- max-w-full --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Form Lọc --}}
                    <form method="GET" action="{{ route('admin.reports.salary-report') }}"
                        class="mb-6 p-4 bg-gray-50 rounded-md border">
                        <p class="text-sm font-medium text-gray-700 mb-2">Lọc báo cáo:</p>
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end"> {{-- Thêm 1 cột cho phòng ban --}}
                            <div>
                                <x-input-label for="month" value="Tháng" />
                                <select name="month" id="month" class="block w-full mt-1 ... rounded-md shadow-sm">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected($filterMonth == $m)>Tháng {{ $m }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <x-input-label for="year" value="Năm" />
                                <x-text-input type="number" name="year" id="year" class="block w-full mt-1" min="2020"
                                    max="{{ now()->year + 1 }}" :value="$filterYear" />
                            </div>
                            <div>
                                <x-input-label for="department_id" value="Phòng Ban" />
                                <select name="department_id" id="department_id"
                                    class="block w-full mt-1 ... rounded-md shadow-sm">
                                    <option value="">-- Tất cả Phòng Ban --</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected($filterDepartment == $department->id)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="employee_id" value="Nhân Viên" />
                                <select name="employee_id" id="employee_id"
                                    class="block w-full mt-1 ... rounded-md shadow-sm">
                                    <option value="">-- Tất cả Nhân Viên --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected($filterEmployee == $employee->id)>
                                            {{ $employee->last_name }} {{ $employee->first_name }}
                                            ({{$employee->employee_code}})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex space-x-2">
                                <x-primary-button type="submit">Xem Báo Cáo</x-primary-button>
                                {{-- <x-secondary-button type="button">Xuất Excel</x-secondary-button> --}}
                            </div>
                        </div>
                    </form>

                    {{-- Bảng Tổng Hợp --}}
                    <div class="mb-6 p-4 bg-indigo-50 rounded-lg border border-indigo-200">
                        <h3 class="text-lg font-semibold text-indigo-800 mb-3">Tổng hợp kỳ
                            {{ $filterMonth }}/{{ $filterYear }}</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 text-sm">
                            <div>
                                <p class="text-gray-600">Số nhân viên có lương:</p>
                                <p class="font-semibold text-lg text-gray-900">{{ $employeeCount }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Tổng Lương Cơ Bản:</p>
                                <p class="font-semibold text-lg text-gray-900">
                                    {{ number_format($totalBaseSalary, 0, ',', '.') }} đ</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Tổng Phụ Cấp:</p>
                                <p class="font-semibold text-lg text-gray-900">
                                    {{ number_format($totalAllowances, 0, ',', '.') }} đ</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Tổng Khấu Trừ:</p>
                                <p class="font-semibold text-lg text-red-600">
                                    {{ number_format($totalDeductions, 0, ',', '.') }} đ</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Tổng Thưởng:</p>
                                <p class="font-semibold text-lg text-green-600">
                                    {{ number_format($totalBonus, 0, ',', '.') }} đ</p>
                            </div>
                            <div>
                                <p class="text-gray-600">Tổng Phạt:</p>
                                <p class="font-semibold text-lg text-red-600">
                                    {{ number_format($totalFines, 0, ',', '.') }} đ</p>
                            </div>
                            <div class="col-span-2 md:col-span-1 lg:col-span-2 border-t pt-2 mt-2">
                                <p class="text-indigo-700 font-semibold">Tổng Lương Thực Trả:</p>
                                <p class="font-bold text-xl text-indigo-700">
                                    {{ number_format($totalNetSalary, 0, ',', '.') }} đ</p>
                            </div>
                        </div>
                    </div>


                    {{-- Bảng Chi Tiết --}}
                    <div class="overflow-x-auto mt-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-3">Chi tiết lương nhân viên</h3>
                        <table class="min-w-full divide-y divide-gray-200 border">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Mã NV</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Nhân Viên</th>
                                    <th
                                        class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Phòng Ban</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Lương CB</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Phụ Cấp</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Khấu Trừ</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Thưởng</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Phạt</th>
                                    <th
                                        class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Thực Nhận</th>
                                    <th
                                        class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                        Trạng Thái</th>
                                    <th class="relative px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($salaries as $salary)
                                                                <tr>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                                        {{ $salary->employee->employee_code ?? 'N/A' }}</td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                        {{ $salary->employee->full_name ?? 'N/A' }}</td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                                                        {{ $salary->employee->department->name ?? 'N/A' }}</td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm text-gray-500">
                                                                        {{ number_format($salary->base_salary, 0, ',', '.') }}</td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm text-gray-500">
                                                                        {{ number_format(($salary->taxable_allowances ?? 0) + ($salary->nontaxable_allowances ?? 0), 0, ',', '.') }}</td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm text-red-500">
                                                                        {{ number_format($salary->deductions, 0, ',', '.') }}</td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm text-green-500">
                                                                        {{ number_format($salary->bonus, 0, ',', '.') }}</td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm text-red-500">
                                                                        {{ number_format($salary->fines, 0, ',', '.') }}</td>
                                                                    <td
                                                                        class="px-4 py-2 whitespace-nowrap text-right text-sm font-semibold text-indigo-600">
                                                                        {{ number_format($salary->net_salary, 0, ',', '.') }}</td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-center">
                                                                    @php
                                                $statusClass = match($salary->status) {
                                                    'paid' => 'bg-green-100 text-green-800',
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'cancelled' => 'bg-red-100 text-red-800',
                                                    default => 'bg-gray-100 text-gray-800',
                                                };
                                                $statusText = match($salary->status) {
                                                    'paid' => 'Đã TT',
                                                    'pending' => 'Chờ TT',
                                                    'cancelled' => 'Đã hủy',
                                                    default => ucfirst($salary->status),
                                                };
                                            @endphp
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                                                 {{ $statusText }}
                                             </span>
                                                                    </td>
                                                                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm">
                                                                        <a href="{{ route('admin.salaries.show', $salary->id) }}"
                                                                            class="text-indigo-600 hover:text-indigo-800">Xem CT</a>
                                                                    </td>
                                                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="px-6 py-4 text-center text-sm text-gray-500">Không có dữ
                                            liệu lương chi tiết cho kỳ đã chọn.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Phân trang cho bảng chi tiết --}}
                    <div class="mt-4">
                        {{ $salaries->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>