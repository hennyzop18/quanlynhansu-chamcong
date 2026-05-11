<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Duyệt Giải Trình Chấm Công') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="mb-6 flex gap-4">
                        <a href="{{ route('admin.attendance-adjustments.index', ['status' => 'pending']) }}" 
                           class="px-4 py-2 rounded {{ $status == 'pending' ? 'bg-indigo-600 text-white' : 'bg-gray-100' }}">Chờ duyệt</a>
                        <a href="{{ route('admin.attendance-adjustments.index', ['status' => 'approved']) }}" 
                           class="px-4 py-2 rounded {{ $status == 'approved' ? 'bg-green-600 text-white' : 'bg-gray-100' }}">Đã duyệt</a>
                        <a href="{{ route('admin.attendance-adjustments.index', ['status' => 'rejected']) }}" 
                           class="px-4 py-2 rounded {{ $status == 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-100' }}">Từ chối</a>
                        <a href="{{ route('admin.attendance-adjustments.index', ['status' => 'all']) }}" 
                           class="px-4 py-2 rounded {{ $status == 'all' ? 'bg-gray-800 text-white' : 'bg-gray-100' }}">Tất cả</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhân viên / Ngày</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loại lỗi</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ gốc (In - Out)</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lý do giải trình</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($adjustments as $adj)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $adj->attendance->employee->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $adj->attendance->date->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 uppercase">
                                        {{ $adj->type }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $adj->attendance->check_in_time?->format('H:i') ?? '--' }} - 
                                        {{ $adj->attendance->check_out_time?->format('H:i') ?? '--' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ Str::limit($adj->reason, 30) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($adj->status == 'pending')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Đang chờ</span>
                                        @elseif($adj->status == 'approved')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Đã duyệt</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Từ chối</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($adj->status == 'pending')
                                        <div class="flex justify-end gap-2">
                                            <form action="{{ route('admin.attendance-adjustments.approve', $adj) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-900">Duyệt</button>
                                            </form>
                                            <form action="{{ route('admin.attendance-adjustments.reject', $adj) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Từ chối</button>
                                            </form>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $adjustments->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
