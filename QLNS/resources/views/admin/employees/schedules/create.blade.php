<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gán Lịch Làm Việc Mới cho') }}: {{ $employee->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="{{ route('admin.employees.schedules.store', $employee) }}">
                        @csrf

                        <!-- Chọn Lịch Làm Việc -->
                        <div>
                            <x-input-label for="work_schedule_id" value="{{ __('Lịch Làm Việc') }}" />
                            <select name="work_schedule_id" id="work_schedule_id"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">-- Chọn lịch --</option>
                                @foreach($workSchedules as $schedule)
                                    <option value="{{ $schedule->id }}" @selected(old('work_schedule_id') == $schedule->id)>
                                        {{ $schedule->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('work_schedule_id')" class="mt-2" />
                        </div>

                        <!-- Ngày Bắt Đầu -->
                        <div class="mt-4">
                            <x-input-label for="start_date" value="{{ __('Ngày Bắt Đầu Áp Dụng') }}" />
                            <x-text-input id="start_date" class="block mt-1 w-full" type="date" name="start_date"
                                :value="old('start_date')" required />
                            <x-input-error :messages="$errors->get('start_date')" class="mt-2" />
                        </div>

                        <!-- Ngày Kết Thúc (Tùy chọn) -->
                        <div class="mt-4">
                            <x-input-label for="end_date"
                                value="{{ __('Ngày Kết Thúc (Để trống nếu đang áp dụng)') }}" />
                            <x-text-input id="end_date" class="block mt-1 w-full" type="date" name="end_date"
                                :value="old('end_date')" />
                            <x-input-error :messages="$errors->get('end_date')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.employees.schedules.index', $employee) }}">
                                <x-secondary-button type="button" class="mr-4">
                                    {{ __('Hủy bỏ') }}
                                </x-secondary-button>
                            </a>
                            <x-primary-button>
                                {{ __('Lưu Gán Lịch') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>