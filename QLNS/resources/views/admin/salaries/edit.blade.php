<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Sửa Chi Tiết Lương') }} - {{ $salary->employee->full_name ?? 'N/A' }} - Kỳ
            {{ $salary->pay_period_start->format('m/Y') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($salary->status !== 'pending')
                        <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded border border-red-200">
                            Chỉ có thể sửa chi tiết bảng lương có trạng thái "Chờ Thanh Toán".
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('admin.salaries.show', $salary) }}">
                                <x-secondary-button>← Quay lại Chi tiết</x-secondary-button>
                            </a>
                        </div>
                    @else
                        {{-- Hiển thị lỗi validation --}}
                        <x-validation-errors class="mb-4" :errors="$errors" />
                        @if(session('error'))
                            <div class="mb-4 font-medium text-sm text-red-600 bg-red-100 p-3 rounded border border-red-200">
                                {{ session('error') }}
                            </div>
                        @endif

                        {{-- Thông tin cố định --}}
                        <div class="mb-6 p-4 bg-gray-50 border rounded-md">
                            <p><strong>Nhân viên:</strong> {{ $salary->employee->full_name ?? 'N/A' }}
                                ({{ $salary->employee->employee_code ?? 'N/A' }})</p>
                            <p><strong>Kỳ lương:</strong> {{ $salary->pay_period_start->format('d/m/Y') }} -
                                {{ $salary->pay_period_end->format('d/m/Y') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('admin.salaries.update', $salary->id) }}">
                            @csrf
                            @method('PUT')

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="base_salary" value="{{ __('Lương Cơ Bản') }}" />
                                    <x-text-input id="base_salary" class="block mt-1 w-full" type="number" step="1000"
                                        name="base_salary" :value="old('base_salary', $salary->base_salary)" required />
                                    <x-input-error :messages="$errors->get('base_salary')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="taxable_allowances" value="{{ __('Phụ Cấp Chịu Thuế (+)') }}" />
                                    <x-text-input id="taxable_allowances" class="block mt-1 w-full" type="number" step="1000"
                                        name="taxable_allowances" :value="old('taxable_allowances', $salary->taxable_allowances)" required />
                                    <x-input-error :messages="$errors->get('taxable_allowances')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="nontaxable_allowances" value="{{ __('Phụ Cấp KHÔNG Thuế (+)') }}" />
                                    <x-text-input id="nontaxable_allowances" class="block mt-1 w-full" type="number" step="1000"
                                        name="nontaxable_allowances" :value="old('nontaxable_allowances', $salary->nontaxable_allowances)" required />
                                    <x-input-error :messages="$errors->get('nontaxable_allowances')" class="mt-2" />
                                </div>
                                @if($salary->salary_type === 'hourly')
                                <div>
                                    <x-input-label for="ot_hours" value="{{ __('Số giờ làm thêm (OT) (+)') }}" />
                                    <x-text-input id="ot_hours" class="block mt-1 w-full" type="number" step="0.5"
                                        name="ot_hours" :value="old('ot_hours', $salary->ot_hours)" />
                                    <x-input-error :messages="$errors->get('ot_hours')" class="mt-2" />
                                </div>
                                @endif
                                <div>
                                    <x-input-label for="deductions" value="{{ __('Khấu Trừ (-)') }}" />
                                    <x-text-input id="deductions" class="block mt-1 w-full" type="number" step="1000"
                                        name="deductions" :value="old('deductions', $salary->deductions)" required />
                                    <x-input-error :messages="$errors->get('deductions')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="bonus" value="{{ __('Thưởng (Khen thưởng) (+)') }}" />
                                    <x-text-input id="bonus" class="block mt-1 w-full" type="number" step="1000"
                                        name="bonus" :value="old('bonus', $salary->bonus)" required />
                                    <x-input-error :messages="$errors->get('bonus')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="fines" value="{{ __('Phạt (Kỷ luật) (-)') }}" />
                                    <x-text-input id="fines" class="block mt-1 w-full" type="number" step="1000"
                                        name="fines" :value="old('fines', $salary->fines)" required />
                                    <x-input-error :messages="$errors->get('fines')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="notes" value="{{ __('Ghi chú') }}" />
                                    <textarea id="notes" name="notes" rows="3"
                                        class="block mt-1 w-full border-gray-300 ... rounded-md shadow-sm">{{ old('notes', $salary->notes) }}</textarea>
                                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                                </div>
                                <div class="md:col-span-2 border-t pt-4">
                                    <span class="font-semibold text-gray-700 text-base">Lương Thực Nhận (Tự động tính
                                        lại):</span>
                                    {{-- Có thể dùng JS để tính toán hiển thị ngay hoặc chỉ hiển thị sau khi lưu --}}
                                    <p class="mt-1 text-indigo-700 font-bold text-lg">
                                        {{ number_format($salary->net_salary, 0, ',', '.') }} đ
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-6">
                                <a href="{{ route('admin.salaries.show', $salary->id) }}">
                                    <x-secondary-button type="button" class="mr-4">
                                        {{ __('Hủy bỏ') }}
                                    </x-secondary-button>
                                </a>
                                <x-primary-button>
                                    {{ __('Lưu Thay Đổi Lương') }}
                                </x-primary-button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>