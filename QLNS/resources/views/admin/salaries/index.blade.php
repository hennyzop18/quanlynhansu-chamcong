<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quản lý Bảng Lương') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8"> {{-- max-w-full cho bảng rộng --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Form Lọc --}}
                    <form method="GET" action="{{ route('admin.salaries.index') }}" class="mb-6 p-4 bg-gray-50 rounded-md border">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="month" value="Tháng" />
                                <select name="month" id="month" class="block w-full mt-1 border-gray-300 ... rounded-md shadow-sm">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected($filterMonth == $m)>Tháng {{ $m }}</option>
                                    @endfor
                                </select>
                            </div>
                            <div>
                                <x-input-label for="year" value="Năm" />
                                <x-text-input type="number" name="year" id="year" class="block w-full mt-1" min="2020" max="{{ now()->year + 1 }}" :value="$filterYear" />
                            </div>
                            <div>
                                <x-input-label for="employee_id" value="Nhân Viên" />
                                <select name="employee_id" id="employee_id" class="block w-full mt-1 border-gray-300 ... rounded-md shadow-sm">
                                    <option value="">-- Tất cả Nhân Viên --</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected($filterEmployee == $employee->id)>
                                            {{ $employee->last_name }} {{ $employee->first_name }} ({{$employee->employee_code}})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex gap-4">
                                <x-primary-button type="submit">Lọc</x-primary-button>
                                <a href="{{ route('admin.salaries.index') }}">
                                    <x-secondary-button type="button">Xóa lọc</x-secondary-button>
                                </a>
                            </div>
                        </div>
                    </form>

                     <div class="mb-4 flex justify-end gap-3">
                         <a href="{{ route('admin.salaries.export-excel', request()->all()) }}">
                             <x-secondary-button class="bg-green-50 text-green-700 border-green-200">
                                 📊 {{ __('Xuất Excel Báo Cáo') }}
                             </x-secondary-button>
                         </a>
                         <a href="{{ route('admin.salaries.create') }}">
                             <x-primary-button>
                                {{ __('Tạo Bảng Lương') }}
                             </x-primary-button>
                         </a>
                    </div>

                     {{-- Hiển thị thông báo thành công/lỗi --}}
                     <x-session-status class="mb-4" :status="session('success')" />
                     @if(session('error'))
                         <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded border border-red-200">
                             {{ session('error') }}
                         </div>
                     @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kỳ Lương</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mã NV</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhân Viên</th>
                                    {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lương CB</th> --}}
                                    {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phụ Cấp</th> --}}
                                    {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Khấu Trừ</th> --}}
                                    {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Thưởng</th> --}}
                                    {{-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phạt</th> --}}
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thực Nhận</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng Thái</th>
                                    <th class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($salaries as $salary)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $salary->pay_period_start->format('m/Y') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $salary->employee->employee_code ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $salary->employee->full_name ?? 'N/A' }}</td>
                                        {{-- <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">{{ number_format($salary->base_salary, 0, ',', '.') }}</td> --}}
                                        {{-- <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">{{ number_format($salary->allowances, 0, ',', '.') }}</td> --}}
                                        {{-- <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">{{ number_format($salary->deductions, 0, ',', '.') }}</td> --}}
                                        {{-- <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">{{ number_format($salary->bonus, 0, ',', '.') }}</td> --}}
                                        {{-- <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">{{ number_format($salary->fines, 0, ',', '.') }}</td> --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold text-indigo-600">{{ number_format($salary->net_salary, 0, ',', '.') }} đ</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            {{-- Status Badge --}}
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
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.salaries.show', $salary) }}" class="text-indigo-600 hover:text-indigo-900">Xem</a>
                                            @if($salary->status == 'pending')
                                                <a href="{{ route('admin.salaries.edit', $salary) }}" class="text-blue-600 hover:text-blue-900">Sửa</a>
                                                 {{-- Form Đánh dấu đã thanh toán --}}
                                                <form action="{{ route('admin.salaries.update', $salary) }}" method="POST" class="inline-block confirm-form" 
                                                    data-title="Xác nhận thanh toán?" data-text="Bạn xác nhận đã thanh toán lương cho {{ $salary->employee->full_name }}?">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="mark_as_paid" value="1">
                                                    <button type="submit" class="text-green-600 hover:text-green-900 text-xs font-semibold">Thanh Toán</button>
                                                </form>
                                                 {{-- Form Hủy bỏ --}}
                                                <form action="{{ route('admin.salaries.update', $salary) }}" method="POST" class="inline-block confirm-form"
                                                    data-title="Hủy bảng lương?" data-text="Bạn chắc chắn muốn hủy bảng lương của {{ $salary->employee->full_name }}?">
                                                     @csrf
                                                     @method('PUT')
                                                     <input type="hidden" name="mark_as_cancelled" value="1">
                                                     <button type="submit" class="text-red-600 hover:text-red-900 text-xs">Hủy Bỏ</button>
                                                </form>
                                            @endif
                                            {{-- Form Xóa vĩnh viễn (nếu cần và status không phải paid) --}}
                                            @if($salary->status != 'paid')
                                            {{-- <form action="{{ route('admin.salaries.destroy', $salary) }}" method="POST" class="inline-block" onsubmit="return confirm('Hành động này sẽ xóa vĩnh viễn. Bạn chắc chắn?');">
                                                 @csrf
                                                 @method('DELETE')
                                                 <x-danger-button type="submit" class="text-xs">Xóa VV</x-danger-button>
                                            </form> --}}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Không tìm thấy bảng lương nào cho kỳ đã chọn.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Phân trang --}}
                    <div class="mt-4">
                        {{ $salaries->appends(request()->query())->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>