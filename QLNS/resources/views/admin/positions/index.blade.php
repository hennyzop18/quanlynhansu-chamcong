<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quản lý Chức vụ') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        openModal: false, 
        viewMode: 'list', 
        currentPos: '', 
        employees: [], 
        selectedEmp: null,
        showList() { this.viewMode = 'list'; this.selectedEmp = null; },
        showDetail(emp) { this.selectedEmp = emp; this.viewMode = 'detail'; }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <div class="mb-4 flex justify-between items-center">
                        <div class="space-x-2">
                            <a href="{{ route('admin.positions.create') }}">
                                <x-primary-button>{{ __('Thêm Chức vụ') }}</x-primary-button>
                            </a>
                            <a href="{{ route('admin.positions.trash') }}">
                                <x-secondary-button class="bg-gray-100 text-gray-700">🗑️ {{ __('Thùng rác') }}</x-secondary-button>
                            </a>
                        </div>
                    </div>

                    <x-session-status class="mb-4" :status="session('success')" />
                    @if(session('error'))
                        <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded border border-red-200">{{ session('error') }}</div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên chức vụ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mô tả</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Số lượng NV</th>
                                    <th class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($positions as $position)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $position->name }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($position->description, 100) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-bold cursor-pointer hover:underline"
                                            @click="currentPos = '{{ $position->name }}'; employees = {{ $position->employees->toJson() }}; showList(); openModal = true">
                                            {{ $position->employees->count() }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.positions.edit', $position) }}" class="text-blue-600 hover:text-blue-900">Sửa</a>
                                            <form action="{{ route('admin.positions.destroy', $position) }}" 
                                                method="POST" 
                                                class="inline-block confirm-form"
                                                data-title="Xóa chức vụ?"
                                                data-text="Bạn có chắc chắn muốn chuyển chức vụ '{{ $position->name }}' vào thùng rác?">
                                                @csrf @method('DELETE')
                                                <x-danger-button type="submit" class="text-xs">Xóa</x-danger-button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">Chưa có chức vụ nào.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $positions->links() }}</div>
                </div>
            </div>
        </div>

        {{-- Modal Đa năng --}}
        <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="openModal = false"><div class="absolute inset-0 bg-gray-500 opacity-75"></div></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    
                    {{-- DANH SÁCH --}}
                    <div x-show="viewMode === 'list'" class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            Nhân viên giữ chức vụ: <span x-text="currentPos" class="text-blue-600"></span>
                        </h3>
                        <div class="mt-2 border-t pt-4">
                            <ul class="divide-y divide-gray-100 max-h-60 overflow-y-auto">
                                <template x-for="emp in employees" :key="emp.id">
                                    <li class="py-3 flex items-center justify-between hover:bg-gray-50 px-2 rounded cursor-pointer" @click="showDetail(emp)">
                                        <div class="flex items-center">
                                            <img :src="emp.avatar_url" class="w-8 h-8 rounded-full mr-3 object-cover border">
                                            <div>
                                                <div class="text-sm font-semibold text-gray-800" x-text="emp.last_name + ' ' + emp.first_name"></div>
                                                <div class="text-xs text-gray-500" x-text="emp.employee_code"></div>
                                            </div>
                                        </div>
                                        <span class="text-blue-500 text-xs">Chi tiết →</span>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>

                    {{-- CHI TIẾT --}}
                    <div x-show="viewMode === 'detail'" class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-center mb-6">
                            <button @click="showList()" class="mr-3 p-2 rounded-full hover:bg-gray-100 text-gray-700 transition-colors" title="Quay lại danh sách">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <h3 class="text-lg font-bold text-gray-900">Thông tin chi tiết</h3>
                        </div>
                        <template x-if="selectedEmp">
                            <div class="text-center">
                                <img :src="selectedEmp.avatar_url" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-blue-50">
                                <h4 class="text-xl font-bold text-gray-900" x-text="selectedEmp.last_name + ' ' + selectedEmp.first_name"></h4>
                                <p class="text-sm text-blue-600 mb-4" x-text="selectedEmp.employee_code"></p>
                                
                                <div class="text-left space-y-3 bg-gray-50 p-4 rounded-lg">
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Email:</span>
                                        <span class="font-medium" x-text="selectedEmp.personal_email || 'Chưa cập nhật'"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Số điện thoại:</span>
                                        <span class="font-medium" x-text="selectedEmp.phone_number || 'Chưa cập nhật'"></span>
                                    </div>
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-500">Chức vụ:</span>
                                        <span class="font-medium" x-text="currentPos"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="openModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>