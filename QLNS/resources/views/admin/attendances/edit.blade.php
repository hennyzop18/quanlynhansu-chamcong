<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sửa Chấm Công') }} - {{ $attendance->employee->full_name ?? 'N/A' }} -
            {{ $attendance->date->format('d/m/Y') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-validation-errors class="mb-4" :errors="$errors" />

                    {{-- Form gửi đến route update, giữ lại query params để quay về đúng bộ lọc --}}
                    <form method="POST"
                        action="{{ route('admin.attendances.update', ['attendance' => $attendance->id] + request()->query()) }}">
                        @csrf
                        @method('PUT')

                        {{-- Thông tin nhân viên (chỉ hiển thị) --}}
                        <div class="mb-4 p-3 bg-gray-50 border rounded-md">
                            <p><strong>Nhân viên:</strong> {{ $attendance->employee->full_name ?? 'N/A' }}
                                ({{ $attendance->employee->employee_code ?? 'N/A' }})</p>
                            <p><strong>Ngày:</strong> {{ $attendance->date->format('d/m/Y') }}</p>
                        </div>


                        {{-- Giờ Check-in (chỉ nhập giờ:phút:giây) --}}
                        <div>
                            <x-input-label for="check_in_time_time" value="{{ __('Giờ Check-in (HH:MM:SS)') }}" />
                            <x-text-input id="check_in_time_time" class="block mt-1 w-full" type="time" step="1"
                                name="check_in_time_time" :value="old('check_in_time_time', $attendance->check_in_time ? $attendance->check_in_time->format('H:i:s') : '')" />
                        </div>

                        {{-- Giờ Check-out (chỉ nhập giờ:phút:giây) --}}
                        <div class="mt-4">
                            <x-input-label for="check_out_time_time" value="{{ __('Giờ Check-out (HH:MM:SS)') }}" />
                            <x-text-input id="check_out_time_time" class="block mt-1 w-full" type="time" step="1"
                                name="check_out_time_time" :value="old('check_out_time_time', $attendance->check_out_time ? $attendance->check_out_time->format('H:i:s') : '')" />
                        </div>


                        <!-- Ghi chú -->
                        <div class="mt-4">
                            <x-input-label for="notes" value="{{ __('Ghi chú (Admin)') }}" />
                            {{-- Chỉ hiển thị note cũ, admin nhập note mới nếu cần --}}
                            <p class="text-sm text-gray-500 mt-1 mb-2">Ghi chú trước đó: {{ $attendance->notes }}</p>
                            <textarea id="notes" name="notes" rows="3"
                                class="block mt-1 w-full border-gray-300 ... rounded-md shadow-sm"
                                placeholder="Thêm ghi chú mới nếu cần...">{{ old('notes') }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            {{-- Link quay lại có query params --}}
                            <a href="{{ route('admin.attendances.index', request()->query()) }}"
                                class="underline ... mr-4">
                                {{ __('Hủy bỏ') }}
                            </a>
                            <x-primary-button>
                                {{ __('Lưu thay đổi') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>