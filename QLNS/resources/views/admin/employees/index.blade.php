<x-admin-layout> {{-- Giả sử dùng layout app của Breeze --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quản lý Nhân sự') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Nút thêm mới và tìm kiếm --}}
                    <div class="mb-4 flex flex-col sm:flex-row justify-between items-start sm:items-center space-y-3 sm:space-y-0">
                        <a href="{{ route('admin.employees.create') }}"
                            class="w-full sm:w-auto text-center inline-flex justify-center items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Thêm Nhân Viên
                        </a>
                        <form method="GET" action="{{ route('admin.employees.index') }}" class="w-full sm:w-auto flex">
                            <input type="text" name="search" placeholder="Tìm kiếm..." value="{{ $search ?? '' }}"
                                class="flex-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-l-md shadow-sm">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-r-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-400 active:bg-blue-600 focus:outline-none focus:border-blue-600 focus:ring ring-blue-300 disabled:opacity-25 transition ease-in-out duration-150">Tìm</button>
                        </form>
                    </div>

                    {{-- Thông báo thành công/lỗi --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-200 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif


                    {{-- Bảng danh sách --}}
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Mã NV</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Họ Tên</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Chức vụ</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày vào làm</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Trạng thái</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Hành động</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($employees as $employee)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $employee->employee_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $employee->full_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $employee->position->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                                    @if($employee->status == 'active') bg-green-100 text-green-800 @elseif($employee->status == 'inactive') bg-yellow-100 text-yellow-800 @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($employee->status == 'active' ? 'Hoạt động' : ($employee->status == 'inactive' ? 'Không hoạt động' : ($employee->status == 'terminated' ? 'Đã chấm dứt' : 'N/A'))) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.employees.show', $employee) }}"
                                                class="text-indigo-600 hover:text-indigo-900 mr-2">Xem</a>
                                            <a href="{{ route('admin.employees.edit', $employee) }}"
                                                class="text-blue-600 hover:text-blue-900 mr-2">Sửa</a>
                                            <form action="{{ route('admin.employees.destroy', $employee) }}" 
                                                method="POST" 
                                                class="inline-block confirm-form"
                                                data-title="Xóa nhân viên?"
                                                data-text="Hành động này sẽ xóa vĩnh viễn dữ liệu của nhân viên '{{ $employee->full_name }}'. Bạn chắc chắn chứ?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Không tìm
                                            thấy nhân viên nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Phân trang --}}
                    <div class="mt-4">
                        {{ $employees->appends(['search' => $search ?? ''])->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>