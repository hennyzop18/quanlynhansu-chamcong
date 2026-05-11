<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Duyệt Đăng Ký Tăng Ca (OT)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <div class="mb-6 flex gap-4">
                        <a href="{{ route('admin.overtime-requests.index', ['status' => 'pending']) }}" 
                           class="px-4 py-2 rounded {{ $status == 'pending' ? 'bg-indigo-600 text-white' : 'bg-gray-100' }}">Chờ duyệt</a>
                        <a href="{{ route('admin.overtime-requests.index', ['status' => 'approved']) }}" 
                           class="px-4 py-2 rounded {{ $status == 'approved' ? 'bg-green-600 text-white' : 'bg-gray-100' }}">Đã duyệt</a>
                        <a href="{{ route('admin.overtime-requests.index', ['status' => 'rejected']) }}" 
                           class="px-4 py-2 rounded {{ $status == 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-100' }}">Từ chối</a>
                        <a href="{{ route('admin.overtime-requests.index', ['status' => 'all']) }}" 
                           class="px-4 py-2 rounded {{ $status == 'all' ? 'bg-gray-800 text-white' : 'bg-gray-100' }}">Tất cả</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhân viên</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tăng ca</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Giờ (Từ - Đến)</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tổng giờ</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lý do</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái</th>
                                    <th class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($requests as $request)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $request->employee->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $request->employee->employee_code }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ substr($request->start_time, 0, 5) }} - {{ substr($request->end_time, 0, 5) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                        {{ $request->hours }}h
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ Str::limit($request->reason, 20) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($request->status == 'pending')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Đang chờ</span>
                                        @elseif($request->status == 'approved')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Đã duyệt</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Từ chối</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        @if($request->status == 'pending')
                                        <div class="flex justify-end gap-2">
                                            <form action="{{ route('admin.overtime-requests.approve', $request) }}" method="POST">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-900">Duyệt</button>
                                            </form>
                                            <form action="{{ route('admin.overtime-requests.reject', $request) }}" method="POST">
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
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
