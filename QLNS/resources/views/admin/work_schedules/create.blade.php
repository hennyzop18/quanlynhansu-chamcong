<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Thêm Ca Làm Việc Mới') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- Hiển thị lỗi validation --}}
                    <x-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="{{ route('admin.work-schedules.store') }}">
                        @csrf
                        <!-- Tên Ca -->
                        <div>
                            <x-input-label for="name" value="{{ __('Tên Ca Làm Việc') }}" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Giờ Bắt Đầu -->
                        <div class="mt-4">
                            <x-input-label for="start_time" value="{{ __('Giờ Bắt Đầu (HH:MM)') }}" />
                            <x-text-input id="start_time" class="block mt-1 w-full" type="time" name="start_time"
                                :value="old('start_time')" required />
                            <x-input-error :messages="$errors->get('start_time')" class="mt-2" />
                        </div>

                        <!-- Giờ Kết Thúc -->
                        <div class="mt-4">
                            <x-input-label for="end_time" value="{{ __('Giờ Kết Thúc (HH:MM)') }}" />
                            <x-text-input id="end_time" class="block mt-1 w-full" type="time" name="end_time"
                                :value="old('end_time')" required />
                            <x-input-error :messages="$errors->get('end_time')" class="mt-2" />
                        </div>

                        <!-- Mốc Đi Trễ (Tùy chọn) -->
                        <div class="mt-4">
                            <x-input-label for="late_threshold"
                                value="{{ __('Mốc Bắt Đầu Tính Đi Trễ (HH:MM - Tùy chọn)') }}" />
                            <x-text-input id="late_threshold" class="block mt-1 w-full" type="time"
                                name="late_threshold" :value="old('late_threshold')" />
                            <x-input-error :messages="$errors->get('late_threshold')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Nếu bỏ trống, sẽ tính trễ khi check-in sau "Giờ Bắt
                                Đầu".</p>
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.work-schedules.index') }}">
                                <x-secondary-button type="button" class="mr-4">
                                    {{ __('Hủy bỏ') }}
                                </x-secondary-button>
                            </a>
                            <x-primary-button>
                                {{ __('Lưu Ca Làm Việc') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>