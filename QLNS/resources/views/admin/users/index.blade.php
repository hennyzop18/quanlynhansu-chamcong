<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quản lý Tài khoản Người Dùng') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        openModal: false, 
        selectedEmp: null,
        showDetail(emp) { 
            this.selectedEmp = emp; 
            this.openModal = true; 
        }
    }">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Form Lọc --}}
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-6 p-4 bg-gray-50 rounded-md border">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                            <div>
                                <x-input-label for="search" value="Tên / Email" />
                                <x-text-input type="text" name="search" id="search" class="block w-full mt-1" :value="$search ?? ''" placeholder="Nhập tên hoặc email..." />
                            </div>
                            <div>
                                <x-input-label for="role" value="Vai trò" />
                                <select name="role" id="role" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                    <option value="">-- Tất cả vai trò --</option>
                                    <option value="admin" @selected($role == 'admin')>Admin</option>
                                    <option value="employee" @selected($role == 'employee')>Employee</option>
                                </select>
                            </div>
                            <div class="flex space-x-2">
                                <x-primary-button type="submit">Lọc</x-primary-button>
                                <a href="{{ route('admin.users.index') }}"><x-secondary-button type="button">Xóa lọc</x-secondary-button></a>
                            </div>
                        </div>
                    </form>

                    <div class="mb-4 flex justify-end">
                        <a href="{{ route('admin.users.create') }}"><x-primary-button>{{ __('Thêm Tài Khoản Mới') }}</x-primary-button></a>
                    </div>

                    <x-session-status class="mb-4" :status="session('success')" />

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tên Người Dùng</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vai trò</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nhân viên liên kết</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Trạng thái TK</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ngày tạo</th>
                                    <th class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($users as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role == 'admin' ? 'bg-indigo-100 text-indigo-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($user->employee)
                                                <span class="text-blue-600 font-bold cursor-pointer hover:underline" 
                                                      @click="showDetail({{ $user->employee->toJson() }})">
                                                    {{ $user->employee->full_name }}
                                                    @if($user->employee->employee_code)
                                                        <span class="text-gray-500 font-normal">({{ $user->employee->employee_code }})</span>
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $user->is_active ? 'Hoạt động' : 'Bị khóa' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-blue-600 hover:text-blue-900">Sửa</a>
                                            @if(Auth::id() !== $user->id && !($user->isAdmin() && \App\Models\User::where('role', 'admin')->count() <= 1))
                                                <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block confirm-form" data-title="Xóa tài khoản?" data-text="Bạn chắc chắn muốn xóa tài khoản '{{ $user->name }}'?">
                                                    @csrf @method('DELETE')
                                                    <x-danger-button type="submit" class="text-xs">Xóa</x-danger-button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Không tìm thấy tài khoản nào.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $users->links() }}</div>
                </div>
            </div>
        </div>

        {{-- Modal Chi tiết nhân viên --}}
        <div x-show="openModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;" x-transition>
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 opacity-75" @click="openModal = false"></div>
                <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full z-50">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6" x-if="selectedEmp">
                        <div class="flex items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-900">Thông tin nhân viên liên kết</h3>
                        </div>
                        <div class="text-center">
                            <img :src="selectedEmp.avatar_url" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover border-4 border-blue-50">
                            <h4 class="text-xl font-bold text-gray-900" x-text="selectedEmp.first_name + ' ' + selectedEmp.last_name"></h4>
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
                                    <span class="text-gray-500">Phòng ban:</span>
                                    <span class="font-medium" x-text="selectedEmp.department ? selectedEmp.department.name : 'N/A'"></span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Chức vụ:</span>
                                    <span class="font-medium" x-text="selectedEmp.position ? selectedEmp.position.name : 'N/A'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between">
                        <a :href="'/admin/employees/' + selectedEmp?.id + '/edit'" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                            Chỉnh sửa hồ sơ
                        </a>
                        <button type="button" @click="openModal = false" class="px-4 py-2 bg-white border rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Đóng</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>