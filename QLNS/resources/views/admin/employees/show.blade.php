<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-row justify-between md:items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Chi Tiết Nhân Viên') }}
            </h2>
            <div class="flex items-center flex-row justify-between gap-4">
                <a href="{{ route('admin.employees.index') }}">
                    <x-secondary-button>
                        ← {{ __('Danh sách Nhân viên') }}
                    </x-secondary-button>
                </a>
                <a href="{{ route('admin.employees.schedules.index', $employee) }}">
                    <x-secondary-button>
                        {{ __('Quản lý Lịch làm việc') }}
                    </x-secondary-button>
                </a>
                <a href="{{ route('admin.employees.edit', $employee->id) }}">
                    <x-primary-button> {{-- Hoặc dùng icon bút chì --}}
                        {{ __('Sửa Thông Tin') }}
                    </x-primary-button>
                </a>

            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                {{-- Phần đầu: Thông tin nhận diện và Ảnh --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 p-8 border-b">
                    {{-- Cột Trái: Tên, Mã & Liên hệ --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div>
                            <h3 class="text-3xl font-extrabold text-gray-900">{{ $employee->full_name }}</h3>
                            <div class="flex items-center mt-2 space-x-4">
                                <span class="text-lg text-blue-600 font-semibold tracking-wider">{{ $employee->employee_code }}</span>
                                @php
                                    $statusClass = match ($employee->status) {
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-yellow-100 text-yellow-800',
                                        'terminated' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800',
                                    };
                                    $statusText = match ($employee->status) {
                                        'active' => 'Hoạt động',
                                        'inactive' => 'Tạm nghỉ',
                                        'terminated' => 'Đã nghỉ việc',
                                        default => ucfirst($employee->status),
                                    };
                                @endphp
                                <span class="px-3 py-1 text-xs font-bold rounded-full {{ $statusClass }}">
                                    {{ $statusText }}
                                </span>
                            </div>
                        </div>

                        <div class="pt-6 border-t border-gray-100">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Thông tin liên hệ</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-6 text-sm text-gray-700">
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-blue-50 rounded-lg text-blue-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    </div>
                                    <span class="font-medium">{{ $employee->phone_number ?? 'Chưa cập nhật' }}</span>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="p-2 bg-blue-50 rounded-lg text-blue-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <span class="font-medium truncate">{{ $employee->personal_email ?? 'Chưa cập nhật' }}</span>
                                </div>
                                @if($employee->user)
                                    <div class="flex items-center space-x-3 md:col-span-2">
                                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-500">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        </div>
                                        <span class="font-medium text-indigo-700">{{ $employee->user->email }} (Tài khoản đăng nhập)</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Cột Phải: Ảnh đại diện --}}
                    <div class="flex justify-center items-center lg:justify-end">
                        <div class="relative">
                            <img src="{{ $employee->avatar ? asset('storage/' . $employee->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($employee->full_name) . '&size=200&background=EBF4FF&color=7F9CF5' }}" 
                                 class="w-44 h-44 rounded-3xl object-cover shadow-2xl border-4 border-white"
                                 alt="{{ $employee->full_name }}">
                            <div class="absolute -bottom-2 -right-2 bg-white p-2 rounded-full shadow-lg border border-gray-100">
                                <div class="w-4 h-4 rounded-full {{ $employee->status === 'active' ? 'bg-green-500' : 'bg-gray-400' }}"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg border">
                    <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Thông tin cá nhân</h4>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Ngày sinh</dt>
                            <dd class="mt-1 text-gray-800">
                                {{ $employee->dob ? \Carbon\Carbon::parse($employee->dob)->format('d/m/Y') : 'N/A' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Giới tính</dt>
                            <dd class="mt-1 text-gray-800">
                                @if($employee->gender == 'male') Nam
                                @elseif($employee->gender == 'female') Nữ
                                @else Khác @endif
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="font-medium text-gray-500">Địa chỉ</dt>
                            <dd class="mt-1 text-gray-800">{{ $employee->address ?? 'N/A' }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Hàng Dưới: Thông tin công việc và lịch sử chấm công --}}
                <div class="bg-gray-50 p-6 rounded-lg border">
                    <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-4">Thông tin công việc
                    </h4>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Chức vụ</dt>
                            <dd class="mt-1 text-gray-800">{{ $employee->position->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Phòng ban</dt>
                            <dd class="mt-1 text-gray-800">{{ $employee->department->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Ngày vào làm</dt>
                            <dd class="mt-1 text-gray-800">
                                {{ \Carbon\Carbon::parse($employee->hire_date)->format('d/m/Y') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="font-medium text-gray-500">Ngày nghỉ việc</dt>
                            <dd class="mt-1 text-gray-800">
                                {{ $employee->termination_date ? \Carbon\Carbon::parse($employee->termination_date)->format('d/m/Y') : '-' }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-gray-50 p-6 rounded-lg border">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Chấm công gần đây</h4>
                        <a href="{{ route('admin.attendances.index', ['employee_id' => $employee->id]) }}"
                            class="text-xs text-blue-600 hover:underline">Xem tất cả</a>
                    </div>
                    @if($employee->attendances->isNotEmpty())
                        <ul class="space-y-2 text-sm text-gray-700">
                            @foreach($employee->attendances as $attendance)
                                <li class="flex justify-between items-center border-b border-gray-200 last:border-b-0 py-2">
                                    <span>{{ $attendance->date->format('d/m/Y') }}</span>
                                    <span>
                                        Vào:
                                        {{ $attendance->check_in_time ? $attendance->check_in_time->format('H:i') : '--:--' }}
                                        |
                                        Ra:
                                        {{ $attendance->check_out_time ? $attendance->check_out_time->format('H:i') : '--:--' }}
                                    </span>
                                    <span
                                        class="text-xs {{ $attendance->status == 'late' ? 'text-yellow-600 font-semibold' : ($attendance->status == 'absent' ? 'text-red-600 font-semibold' : '') }}">
                                        {{ $attendance->status == 'late' ? 'Đi trễ' : ($attendance->status == 'absent' ? 'Vắng' : '') }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500 italic">Chưa có dữ liệu chấm công.</p>
                    @endif
                </div>

            </div>
        </div>
    </div>
    </div>
</x-admin-layout>