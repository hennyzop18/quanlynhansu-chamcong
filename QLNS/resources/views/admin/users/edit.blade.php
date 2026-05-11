<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sửa Tài Khoản') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- Hiển thị lỗi validation --}}
                    <x-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                        @csrf
                        @method('PUT') {{-- Hoặc PATCH --}}

                        <!-- Tên Người Dùng -->
                        <div>
                            <x-input-label for="name" value="{{ __('Tên Người Dùng') }}" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name', $user->name)" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="mt-4">
                            <x-input-label for="email" value="{{ __('Email') }}" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                :value="old('email', $user->email)" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Mật Khẩu -->
                        <div class="mt-4">
                            <x-input-label for="password" value="{{ __('Mật khẩu mới (Để trống nếu không đổi)') }}" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                                autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Xác Nhận Mật Khẩu -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" value="{{ __('Xác nhận Mật khẩu mới') }}" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                                name="password_confirmation" autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <!-- Vai trò -->
                        <div class="mt-4">
                            <x-input-label for="role" value="{{ __('Vai trò') }}" />
                            <select name="role" id="role"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required {{ Auth::id() === $user->id ? 'disabled' : '' }}> {{-- Không cho sửa role của
                                chính mình --}}
                                <option value="">-- Chọn vai trò --</option>
                                <option value="admin" @selected(old('role', $user->role) == 'admin')>Admin</option>
                                <option value="employee" @selected(old('role', $user->role) == 'employee')>Employee
                                </option>
                            </select>
                            @if(Auth::id() === $user->id)
                                <input type="hidden" name="role" value="{{ $user->role }}"> {{-- Gửi role ẩn nếu bị disable
                                --}}
                                <p class="text-xs text-red-600 mt-1">Bạn không thể thay đổi vai trò của chính mình.</p>
                            @endif
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Liên kết Nhân viên (Chỉ hiện khi chọn Role Employee) -->
                        <div class="mt-4" id="employee-link-section"
                            style="{{ old('role', $user->role) === 'employee' ? '' : 'display:none;' }}">
                            <x-input-label for="employee_id"
                                value="{{ __('Liên kết với Nhân viên (Nếu là Role Employee)') }}" />
                            <select name="employee_id" id="employee_id"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Chọn nhân viên --</option>
                                {{-- Hiển thị NV hiện tại và các NV chưa có TK --}}
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected(old('employee_id', $user->employee_id) == $employee->id)>
                                        {{ $employee->last_name }} {{ $employee->first_name }}
                                        ({{$employee->employee_code}})
                                        {{ $employee->user && $employee->user->id !== $user->id ? '(Đã có TK)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
                        </div>

                        <!-- Trạng thái Tài khoản -->
                        <div class="mt-4">
                            <x-input-label for="is_active" value="{{ __('Trạng thái Tài khoản') }}" />
                            <select name="is_active" id="is_active"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required {{ Auth::id() === $user->id ? 'disabled' : '' }}> {{-- Không cho tự khóa TK của
                                mình --}}
                                <option value="1" @selected(old('is_active', $user->is_active) == 1)>Hoạt động</option>
                                <option value="0" @selected(old('is_active', $user->is_active) == 0)>Khóa</option>
                            </select>
                            @if(Auth::id() === $user->id)
                                <input type="hidden" name="is_active" value="{{ $user->is_active ? 1 : 0 }}">
                                <p class="text-xs text-red-600 mt-1">Bạn không thể tự khóa tài khoản của mình.</p>
                            @endif
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}">
                                <x-secondary-button type="button" class="mr-4">
                                    {{ __('Hủy bỏ') }}
                                </x-secondary-button>
                            </a>
                            <x-primary-button>
                                {{ __('Lưu Thay Đổi') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- Script ẩn/hiện chọn Employee dựa trên Role (giống create) --}}
    @push('scripts')
        <script>
            // ... (copy script từ create.blade.php) ...
            // Chạy lần đầu khi load trang edit
            toggleEmployeeLink();
        </script>
    @endpush
</x-admin-layout>