<x-employee-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Đăng Ký Nghỉ Phép') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    
                    <form method="POST" action="{{ route('leaves.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="type" value="Loại nghỉ" />
                            <select name="type" id="type" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                <option value="annual">Phép năm</option>
                                <option value="sick">Nghỉ ốm</option>
                                <option value="unpaid">Nghỉ không lương</option>
                                <option value="marriage">Kết hôn</option>
                                <option value="funeral">Tang chế</option>
                                <option value="other">Khác</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <x-input-label for="start_date" value="Từ ngày" />
                                <x-text-input type="date" name="start_date" id="start_date" class="block w-full mt-1" required />
                            </div>
                            <div>
                                <x-input-label for="end_date" value="Đến ngày" />
                                <x-text-input type="date" name="end_date" id="end_date" class="block w-full mt-1" required />
                            </div>
                        </div>

                        <div class="mb-6">
                            <x-input-label for="reason" value="Lý do" />
                            <textarea name="reason" id="reason" rows="3" class="block w-full mt-1 border-gray-300 rounded-md shadow-sm" placeholder="Nhập lý do nghỉ..."></textarea>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('leaves.index') }}">
                                <x-secondary-button type="button">Hủy</x-secondary-button>
                            </a>
                            <x-primary-button>Gửi yêu cầu</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-employee-layout>
