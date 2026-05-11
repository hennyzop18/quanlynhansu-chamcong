<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Chi Tiết Chức Vụ') }}: {{ $position->name }}
            </h2>
            <a href="{{ route('admin.positions.index') }}">
                <x-secondary-button>
                    ← {{ __('Danh sách Chức vụ') }}
                </x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                    <div>
                        <span class="font-semibold text-gray-600">Tên Chức Vụ:</span>
                        <p class="text-gray-900 text-lg">{{ $position->name }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-600">Mô tả:</span>
                        <p class="text-gray-900">{{ $position->description ?? 'Không có mô tả.' }}</p>
                    </div>
                    {{-- Có thể thêm thông tin khác nếu cần, ví dụ: số lượng nhân viên --}}
                    <div>
                        <span class="font-semibold text-gray-600">Số lượng nhân viên:</span>
                        <p class="text-gray-900">{{ $position->employees()->count() }}</p> {{-- Query count nếu chưa
                        load --}}
                    </div>

                    <div class="flex items-center justify-end mt-6 border-t pt-4">
                        <a href="{{ route('admin.positions.edit', $position->id) }}">
                            <x-primary-button>
                                {{ __('Sửa') }}
                            </x-primary-button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>