<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sửa Ghi Nhận Khen thưởng/Kỷ luật') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    {{-- Hiển thị lỗi validation --}}
                    <x-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="{{ route('admin.rewards-discipline.update', $rewardDiscipline->id) }}">
                        @csrf
                        @method('PUT') {{-- Hoặc PATCH --}}

                        <!-- Chọn Nhân Viên -->
                        <div class="mt-4">
                            <x-input-label for="employee_id" value="{{ __('Nhân Viên') }}" />
                            <select name="employee_id" id="employee_id"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">-- Chọn nhân viên --</option>
                                @foreach($employees as $employee) {{-- Truyền $employees từ controller --}}
                                    <option value="{{ $employee->id }}" @selected(old('employee_id', $rewardDiscipline->employee_id) == $employee->id)>
                                        {{ $employee->last_name }} {{ $employee->first_name }}
                                        ({{$employee->employee_code}})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('employee_id')" class="mt-2" />
                        </div>

                        <!-- Loại (Khen thưởng / Kỷ luật) -->
                        <div class="mt-4">
                            <x-input-label for="type" value="{{ __('Loại Ghi Nhận') }}" />
                            <select name="type" id="type"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>
                                <option value="">-- Chọn loại --</option>
                                <option value="reward" @selected(old('type', $rewardDiscipline->type) == 'reward')>Khen
                                    thưởng</option>
                                <option value="discipline" @selected(old('type', $rewardDiscipline->type) == 'discipline')>Kỷ luật</option>
                            </select>
                            <x-input-error :messages="$errors->get('type')" class="mt-2" />
                        </div>

                        <!-- Ngày Ghi Nhận -->
                        <div class="mt-4">
                            <x-input-label for="date" value="{{ __('Ngày Ghi Nhận') }}" />
                            <x-text-input id="date" class="block mt-1 w-full" type="date" name="date"
                                :value="old('date', $rewardDiscipline->date->format('Y-m-d'))" required />
                            <x-input-error :messages="$errors->get('date')" class="mt-2" />
                        </div>

                        <!-- Lý Do -->
                        <div class="mt-4">
                            <x-input-label for="reason" value="{{ __('Lý Do') }}" />
                            <textarea id="reason" name="reason" rows="4"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                required>{{ old('reason', $rewardDiscipline->reason) }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <!-- Số Tiền (Tùy chọn) -->
                        <div class="mt-4">
                            <x-input-label for="amount" value="{{ __('Số Tiền (VNĐ - Để trống nếu không có)') }}" />
                            <x-text-input id="amount" class="block mt-1 w-full" type="number" step="1000" name="amount"
                                :value="old('amount', $rewardDiscipline->amount)" />
                            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                            <p class="text-sm text-gray-500 mt-1">Số tiền này sẽ ảnh hưởng đến bảng lương (Thưởng/Phạt).
                            </p>
                        </div>


                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.rewards-discipline.index') }}">
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
</x-admin-layout>