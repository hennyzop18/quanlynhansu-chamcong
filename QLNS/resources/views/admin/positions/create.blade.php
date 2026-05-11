<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Thêm Chức Vụ Mới') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-validation-errors class="mb-4" :errors="$errors" />

                    <form method="POST" action="{{ route('admin.positions.store') }}">
                        @csrf
                        <!-- Tên Chức Vụ -->
                        <div>
                            <x-input-label for="name" value="{{ __('Tên Chức Vụ') }}" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')"
                                required autofocus />
                        </div>

                        <!-- Mô tả -->
                        <div class="mt-4">
                            <x-input-label for="description" value="{{ __('Mô tả') }}" />
                            <textarea id="description" name="description" rows="3"
                                class="block mt-1 w-full border-gray-300 ... rounded-md shadow-sm">{{ old('description') }}</textarea>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('admin.positions.index') }}" class="underline ... mr-4">
                                {{ __('Hủy bỏ') }}
                            </a>
                            <x-primary-button>
                                {{ __('Lưu Chức Vụ') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>