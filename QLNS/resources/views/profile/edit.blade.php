<x-employee-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Thông tin cá nhân') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Form Cập Nhật Thông Tin --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <form method="post" action="{{ route('employee.profile.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            {{-- =============================================== --}}
                            {{-- PHẦN THÔNG TIN NHÂN VIÊN (EMPLOYEE)              --}}
                            {{-- =============================================== --}}
                            <header>
                                <h2 class="text-lg font-medium text-gray-900">
                                    {{ __('Thông tin nhân viên') }}
                                </h2>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __("Cập nhật thông tin cá nhân của bạn. Các trường bị làm mờ không thể thay đổi.") }}
                                </p>
                            </header>

                            <!-- Mã Nhân Viên (Không sửa) -->
                            <div class="mt-4">
                                <x-input-label for="employee_code" :value="__('Mã Nhân Viên')" />
                                <x-text-input id="employee_code" class="block mt-1 w-full bg-gray-100" type="text" :value="$employee->employee_code" disabled readonly />
                            </div>

                            <!-- Họ và tên (Cho phép sửa) -->
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="last_name" :value="__('Họ')" />
                                    <x-text-input id="last_name" name="last_name" type="text" class="mt-1 block w-full" :value="old('last_name', $employee->last_name)" required oninput="updateDisplayName()" />
                                    <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
                                </div>
                                <div>
                                    <x-input-label for="first_name" :value="__('Tên')" />
                                    <x-text-input id="first_name" name="first_name" type="text" class="mt-1 block w-full" :value="old('first_name', $employee->first_name)" required oninput="updateDisplayName()" />
                                    <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
                                </div>
                            </div>

                            <!-- Số điện thoại (Được sửa) -->
                            <div class="mt-4">
                                <x-input-label for="phone_number" :value="__('Số điện thoại')" />
                                <x-text-input id="phone_number" name="phone_number" type="tel" class="mt-1 block w-full" :value="old('phone_number', $employee->phone_number)" />
                                <x-input-error class="mt-2" :messages="$errors->get('phone_number')" />
                            </div>

                            <!-- Email cá nhân (Được sửa) -->
                            <div class="mt-4">
                                 <x-input-label for="personal_email" :value="__('Email cá nhân')" />
                                 <x-text-input id="personal_email" name="personal_email" type="email" class="mt-1 block w-full" :value="old('personal_email', $employee->personal_email)" />
                                 <x-input-error class="mt-2" :messages="$errors->get('personal_email')" />
                            </div>

                            <!-- Địa chỉ (Được sửa) -->
                            <div class="mt-4">
                                <x-input-label for="address" :value="__('Địa chỉ')" />
                                <textarea id="address" name="address" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('address', $employee->address) }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('address')" />
                            </div>


                            {{-- =============================================== --}}
                            {{-- PHẦN THÔNG TIN TÀI KHOẢN (USER)                 --}}
                            {{-- =============================================== --}}
                            <hr class="my-6">
                            <header>
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ __('Thông tin tài khoản') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __("Cập nhật email đăng nhập (Tên hiển thị tự động lấy từ Họ và tên).") }}
                                </p>
                            </header>

                            <!-- Tên hiển thị (Tự động) -->
                            <div>
                                <x-input-label for="name" :value="__('Tên hiển thị (Tự động)')" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full bg-gray-100" :value="old('name', $user->name)" readonly />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <!-- Email đăng nhập -->
                            <div>
                                <x-input-label for="email" :value="__('Email đăng nhập')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            {{-- =============================================== --}}
                            {{-- PHẦN ĐỔI MẬT KHẨU                             --}}
                            {{-- =============================================== --}}
                            <hr class="my-6">
                            <header>
                               <h3 class="text-lg font-medium text-gray-900">
                                   {{ __('Đổi mật khẩu') }}
                               </h3>
                                <p class="mt-1 text-sm text-gray-600">
                                    {{ __("Để trống tất cả các trường mật khẩu nếu bạn không muốn thay đổi.") }}
                                </p>
                            </header>

                             <!-- Mật khẩu hiện tại -->
                             <div class="mt-4">
                                 <x-input-label for="current_password" :value="__('Mật khẩu hiện tại')" />
                                 <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
                                 <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                             </div>

                             <!-- Mật khẩu mới -->
                             <div class="mt-4">
                                 <x-input-label for="password" :value="__('Mật khẩu mới')" />
                                 <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
                                 <x-input-error :messages="$errors->get('password')" class="mt-2" />
                             </div>

                             <!-- Xác nhận mật khẩu mới -->
                             <div class="mt-4">
                                 <x-input-label for="password_confirmation" :value="__('Xác nhận mật khẩu mới')" />
                                 <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password"/>
                                 <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                             </div>


                            <div class="flex items-center gap-4 mt-6">
                                <x-primary-button>{{ __('Lưu thay đổi') }}</x-primary-button>

                                @if (session('status') === 'profile-updated')
                                    <p
                                        x-data="{ show: true }"
                                        x-show="show"
                                        x-transition
                                        x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600"
                                    >{{ __('Đã lưu.') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateDisplayName() {
            const firstName = document.getElementById('first_name').value;
            const lastName = document.getElementById('last_name').value;
            document.getElementById('name').value = lastName + (firstName ? ' ' + firstName : '');
        }
    </script>
</x-employee-layout>