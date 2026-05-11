<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-row justify-between md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Lịch Làm Việc Đã Gán cho') }}: {{ $employee->full_name }}
            </h2>
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.employees.show', $employee) }}">
                    <x-secondary-button>
                        ← {{ __('Xem Chi Tiết NV') }}
                    </x-secondary-button>
                </a>
                <a href="{{ route('admin.employees.schedules.create', $employee) }}">
                    <x-primary-button>
                        {{ __('Gán Lịch Mới') }}
                    </x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <x-session-status class="mb-4" :status="session('success')" />
                    {{-- Hiển thị lỗi nếu có --}}

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tên Lịch Làm Việc</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày Bắt Đầu</th>
                                    <th scope="col"
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày Kết Thúc</th>
                                    <th scope="col" class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($schedules as $scheduleAssignment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $scheduleAssignment->workSchedule->name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $scheduleAssignment->start_date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $scheduleAssignment->end_date ? $scheduleAssignment->end_date->format('d/m/Y') : 'Đang áp dụng' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.employees.schedules.edit', [$employee, $scheduleAssignment]) }}"
                                                class="text-blue-600 hover:text-blue-900">Sửa</a>
                                            <form
                                                action="{{ route('admin.employees.schedules.destroy', [$employee, $scheduleAssignment]) }}"
                                                method="POST" class="inline-block"
                                                onsubmit="return confirm('Bạn chắc chắn muốn xóa lịch gán này?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button type="submit" class="text-xs">Xóa</x-danger-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Chưa gán lịch
                                            làm việc nào cho nhân viên này.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Phân trang nếu có --}}
                    @if ($schedules instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-4">
                            {{ $schedules->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>