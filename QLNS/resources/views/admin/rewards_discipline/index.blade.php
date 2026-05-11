<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quản lý Khen thưởng / Kỷ luật') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8"> {{-- max-w-full cho bảng rộng --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Form Lọc --}}
                    <form method="GET" action="{{ route('admin.rewards-discipline.index') }}"
                        class="mb-6 p-4 bg-gray-50 rounded-md border">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="employee_id" value="Nhân Viên" />
                                <select name="employee_id" id="employee_id"
                                    class="block w-full mt-1 border-gray-300 ... rounded-md shadow-sm">
                                    <option value="">-- Tất cả Nhân Viên --</option>
                                    @foreach($employees as $employee) {{-- Truyền $employees từ controller --}}
                                        <option value="{{ $employee->id }}" @selected($filterEmployee == $employee->id)>
                                            {{ $employee->last_name }} {{ $employee->first_name }}
                                            ({{$employee->employee_code}})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="type" value="Loại" />
                                <select name="type" id="type"
                                    class="block w-full mt-1 border-gray-300 ... rounded-md shadow-sm">
                                    <option value="">-- Tất cả --</option>
                                    <option value="reward" @selected($filterType == 'reward')>Khen thưởng</option>
                                    <option value="discipline" @selected($filterType == 'discipline')>Kỷ luật</option>
                                </select>
                            </div>
                            {{-- TODO: Thêm lọc theo ngày tháng nếu cần --}}
                            {{-- <div>
                                <x-input-label for="date_from" value="Từ Ngày" />
                                <x-text-input type="date" name="date_from" id="date_from" class="block w-full mt-1"
                                    :value="$filterDateFrom ?? ''" />
                            </div>
                            <div>
                                <x-input-label for="date_to" value="Đến Ngày" />
                                <x-text-input type="date" name="date_to" id="date_to" class="block w-full mt-1"
                                    :value="$filterDateTo ?? ''" />
                            </div> --}}
                            <div class="flex gap-4">
                                <x-primary-button type="submit">Lọc</x-primary-button>
                                <a href="{{ route('admin.rewards-discipline.index') }}">
                                    <x-secondary-button type="button">Xóa lọc</x-secondary-button>
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="mb-4 flex justify-end">
                        <a href="{{ route('admin.rewards-discipline.create') }}">
                            <x-primary-button>
                                {{ __('Thêm Ghi Nhận Mới') }}
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
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Ngày</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nhân Viên</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Loại</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Lý do</th>
                                    <th
                                        class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Số Tiền (VNĐ)</th>
                                    <th class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($rewardsDiscipline as $record)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $record->date->format('d/m/Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $record->employee->full_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($record->type == 'reward')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Khen thưởng
                                                </span>
                                            @elseif($record->type == 'discipline')
                                                <span
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Kỷ luật
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-normal text-sm text-gray-500 max-w-sm">
                                            {{ Str::limit($record->reason, 150) }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold {{ $record->type == 'reward' ? 'text-green-600' : ($record->type == 'discipline' ? 'text-red-600' : 'text-gray-600') }}">
                                            {{ $record->amount !== null ? number_format($record->amount, 0, ',', '.') : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            {{-- <a href="{{ route('admin.rewards-discipline.show', $record->id) }}"
                                                class="text-indigo-600 hover:text-indigo-900">Xem</a> --}}
                                            <a href="{{ route('admin.rewards-discipline.edit', $record->id) }}"
                                                class="text-blue-600 hover:text-blue-900">Sửa</a>
                                            <form action="{{ route('admin.rewards-discipline.destroy', $record->id) }}"
                                                method="POST" class="inline-block"
                                                onsubmit="return confirm('Bạn chắc chắn muốn xóa ghi nhận này?');">
                                                @csrf
                                                @method('DELETE')
                                                <x-danger-button type="submit" class="text-xs">Xóa</x-danger-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Không tìm thấy
                                            ghi nhận nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Phân trang --}}
                    @if ($rewardsDiscipline instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="mt-4">
                            {{ $rewardsDiscipline->appends(request()->query())->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-admin-layout>