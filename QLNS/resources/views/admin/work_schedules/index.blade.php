<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quản lý Ca Làm Việc') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="mb-4 flex justify-between items-center">
                        <div class="space-x-2">
                            <a href="{{ route('admin.work-schedules.create') }}">
                                <x-primary-button>
                                    {{ __('Thêm Ca Mới') }}
                                </x-primary-button>
                            </a>
                            <a href="{{ route('admin.work-schedules.trash') }}">
                                <x-secondary-button class="bg-gray-100 text-gray-700">
                                    🗑️ {{ __('Thùng rác') }}
                                </x-secondary-button>
                            </a>
                        </div>
                    </div>

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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên Ca</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ Bắt Đầu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ Kết Thúc</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mốc Đi Trễ</th>
                                    <th class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($schedules as $schedule)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $schedule->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $schedule->late_threshold ? \Carbon\Carbon::parse($schedule->late_threshold)->format('H:i') : '-' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.work-schedules.edit', $schedule) }}" class="text-blue-600 hover:text-blue-900">Sửa</a>
                                            <form action="{{ route('admin.work-schedules.destroy', $schedule) }}" 
                                                method="POST" 
                                                class="inline-block confirm-form"
                                                data-title="Xóa ca làm việc?"
                                                data-text="Bạn có chắc chắn muốn chuyển ca '{{ $schedule->name }}' vào thùng rác?">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button type="submit" class="text-xs">Xóa</x-danger-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Chưa có ca làm việc nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">{{ $schedules->links() }}</div>

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>