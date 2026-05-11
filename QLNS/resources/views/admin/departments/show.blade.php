<x-admin-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Chi Tiết Phòng Ban') }}: {{ $department->name }}
            </h2>
            <a href="{{ route('admin.departments.index') }}">
                <x-secondary-button>
                    ← {{ __('Danh sách Phòng ban') }}
                </x-secondary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 space-y-4">
                    <div>
                        <span class="font-semibold text-gray-600">Tên Phòng Ban:</span>
                        <p class="text-gray-900 text-lg">{{ $department->name }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-600">Mô tả:</span>
                        <p class="text-gray-900">{{ $department->description ?? 'Không có mô tả.' }}</p>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-600">Số lượng nhân viên:</span>
                        {{-- Giả sử bạn load count trong controller nếu cần --}}
                        <p class="text-gray-900">{{ $department->employees_count ?? $department->employees()->count() }}
                        </p>
                    </div>
                    {{-- Có thể thêm danh sách nhân viên thuộc phòng ban này --}}

                    <div class="flex items-center justify-end mt-6 border-t pt-4">
                        <a href="{{ route('admin.departments.edit', $department->id) }}">
                            <x-button>
                                {{ __('Sửa') }}
                            </x-button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>