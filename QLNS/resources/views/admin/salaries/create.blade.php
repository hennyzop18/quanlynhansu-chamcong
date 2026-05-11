<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tạo / Cập Nhật Bảng Lương') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    <x-validation-errors class="mb-4" :errors="$errors" />
                    @if(session('error'))
                        <div class="mb-4 text-sm text-red-600 bg-red-100 p-3 rounded border border-red-200">
                            {{ session('error') }}
                        </div>
                    @endif

                    <p class="mb-6 text-sm text-gray-600">
                        Tạo hoặc cập nhật bảng lương cho toàn bộ nhân viên đủ điều kiện trong kỳ được chọn.
                        Nếu bảng lương đã tồn tại, dữ liệu sẽ được tính lại (updateOrCreate).
                    </p>

                    <form method="POST" action="{{ route('admin.salaries.store') }}">
                        @csrf

                        {{-- ── Kỳ lương ── --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="month" value="{{ __('Tháng') }}" />
                                <select name="month" id="month"
                                    class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required>
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option value="{{ $m }}" @selected(old('month', now()->month) == $m)>Tháng {{ $m }}</option>
                                    @endfor
                                </select>
                                <x-input-error :messages="$errors->get('month')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="year" value="{{ __('Năm') }}" />
                                <x-text-input type="number" name="year" id="year" class="block mt-1 w-full"
                                    min="2020" max="{{ now()->year + 1 }}" :value="old('year', now()->year)" required />
                                <x-input-error :messages="$errors->get('year')" class="mt-2" />
                            </div>
                        </div>

                        {{-- ── Số ngày công chuẩn ── --}}
                        <div class="mt-5">
                            <x-input-label for="standard_work_days" value="{{ __('Số ngày công chuẩn') }}" />
                            <x-text-input type="number" name="standard_work_days" id="standard_work_days"
                                class="block mt-1 w-full" min="1" max="31" :value="old('standard_work_days', 22)" />
                            <x-input-error :messages="$errors->get('standard_work_days')" class="mt-2" />
                            <p class="text-xs text-gray-500 mt-1">Mặc định 22 ngày/tháng. Điều chỉnh nếu tháng Tết hoặc có ngày nghỉ bù.</p>
                        </div>

                        {{-- ── Chế độ lương ghi đè ── --}}
                        <div class="mt-6 pt-5 border-t border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-700 mb-1">Chế độ tính lương</h3>
                            <p class="text-xs text-gray-500 mb-3">
                                Mặc định: mỗi nhân viên được tính theo chế độ đã cấu hình riêng
                                (👔 Lương cố định / ⏱ Lương theo giờ).<br>
                                Nếu nhập <strong>Lương ghi đè</strong>, <em>tất cả</em> nhân viên sẽ dùng mức này thay cho cấu hình riêng.
                            </p>

                            {{-- Toggle --}}
                            <div class="flex items-center gap-3 mb-4">
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="use_override" class="sr-only peer"
                                        @checked(old('override_salary'))
                                        onchange="toggleOverride(this.checked)">
                                    <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-indigo-400 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                                    <span class="ms-3 text-sm font-medium text-gray-700">Ghi đè lương đồng loạt</span>
                                </label>
                            </div>

                            <div id="override_field" style="{{ old('override_salary') ? '' : 'display:none;' }}">
                                <x-input-label for="override_salary" value="{{ __('Mức lương ghi đè (đ/người/tháng)') }}" />
                                <x-text-input type="number" name="override_salary" id="override_salary"
                                    class="block mt-1 w-full" min="0" step="100000"
                                    :value="old('override_salary')"
                                    placeholder="ví dụ: 6000000" />
                                <x-input-error :messages="$errors->get('override_salary')" class="mt-2" />
                                <p class="text-xs text-amber-600 mt-1">
                                    ⚠ Lương này sẽ áp dụng cho <strong>tất cả nhân viên</strong> trong kỳ,
                                    bỏ qua lương cơ bản & đơn giá giờ riêng. Admin chịu trách nhiệm kiểm tra lại.
                                </p>
                            </div>
                        </div>

                        {{-- ── Submit ── --}}
                        <div class="flex items-center justify-end mt-8 gap-3">
                            <a href="{{ route('admin.salaries.index') }}">
                                <x-secondary-button type="button">Hủy</x-secondary-button>
                            </a>
                            <x-primary-button>
                                🧮 {{ __('Tạo / Cập Nhật Bảng Lương') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@push('scripts')
<script>
function toggleOverride(enabled) {
    document.getElementById('override_field').style.display = enabled ? '' : 'none';
    if (!enabled) document.getElementById('override_salary').value = '';
}
</script>
@endpush
</x-admin-layout>