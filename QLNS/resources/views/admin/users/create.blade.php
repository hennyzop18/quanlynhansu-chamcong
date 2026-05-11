<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Thêm Tài Khoản Mới') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- Hiển thị lỗi validation --}}
                    <x-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <!-- Tên Người Dùng -->
                        <div>
                            <x-input-label for="name" value="{{ __('Tên Người Dùng') }}" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name')" required autofocus autocomplete="name" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="mt-4">
                            <x-input-label for="email" value="{{ __('Email') }}" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                :value="old('email')" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Mật Khẩu -->
                        <div class="mt-4">
                            <x-input-label for="password" value="{{ __('Mật khẩu') }}" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                                required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Xác Nhận Mật Khẩu -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" value="{{ __('Xác nhận Mật khẩu') }}" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                                name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <!-- Vai trò -->
                        <div class="mt-4">
                            <x-input-label for="role" value="{{ __('Vai trò') }}" />
                            <select name="role" id="role"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">-- Chọn vai trò --</option>
                                <option value="admin" @selected(old('role') == 'admin')>Admin</option>
                                <option value="employee" @selected(old('role') == 'employee')>Employee</option>
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Liên kết Nhân viên (Chỉ hiện khi chọn Role Employee - dùng JS hoặc để validate xử lý) -->
                        {{-- Cần JS để ẩn/hiện hoặc dựa vào validation backend --}}
                        <div class="mt-4" id="employee-link-section"
                            style="{{ old('role', 'admin') === 'employee' ? '' : 'display:none;' }}">
                            <x-input-label for="employee_id"
                                value="{{ __('Liên kết với Nhân viên (Nếu là Role Employee)') }}" />
                            <select name="employee_id" id="employee_id"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">-- Chọn nhân viên chưa có tài khoản --</option>
                                @foreach($employees as $employee) {{-- Truyền $employees (chưa có TK) từ controller --}}
                                    <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>
                                        {{ $employee->last_name }} {{ $employee->first_name }}
                                        ({{$employee->employee_code}})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.users.index') }}">
                                <x-secondary-button type="button" class="mr-4">
                                    {{ __('Hủy bỏ') }}
                                </x-secondary-button>
                            </a>
                            <x-primary-button>
                                {{ __('Lưu Tài Khoản') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Script đơn giản để ẩn/hiện chọn Employee dựa trên Role --}}
    @push('scripts')
        <script>
            const roleSelect = document.getElementById('role');
            const employeeSection = document.getElementById('employee-link-section');
            const employeeSelect = document.getElementById('employee_id');

            function toggleEmployeeLink() {
                if (roleSelect.value === 'employee') {
                    employeeSection.style.display = 'block';
                    employeeSelect.required = true; // Thêm required nếu là Employee
                } else {
                    employeeSection.style.display = 'none';
                    employeeSelect.required = false; // Bỏ required nếu không phải Employee
                    employeeSelect.value = ''; // Reset giá trị
                }
            }

            roleSelect.addEventListener('change', toggleEmployeeLink);
            // Chạy lần đầu khi load trang
            // toggleEmployeeLink(); // Bỏ comment nếu ban đầu không display:none
        </script>
    @endpush

</x-admin-layout>