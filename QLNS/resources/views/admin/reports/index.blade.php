<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Thống kê & Báo cáo') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Chọn loại báo cáo:</h3>
                    <ul class="list-disc list-inside space-y-2">
                        <li>
                            <a href="{{ route('admin.reports.employee-statistics') }}"
                                class="text-blue-600 hover:text-blue-800 hover:underline">
                                Thống kê nhân sự (Theo phòng ban, chức vụ)
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.attendance-report') }}"
                                class="text-blue-600 hover:text-blue-800 hover:underline">
                                Báo cáo chấm công theo tháng
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.salary-report') }}"
                                class="text-blue-600 hover:text-blue-800 hover:underline">
                                Báo cáo tổng hợp lương
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.reports.attendance-department') }}"
                                class="text-blue-600 hover:text-blue-800 hover:underline">
                                Báo cáo tỷ lệ chuyên cần theo phòng ban (Mới)
                            </a>
                        </li>
                        {{-- Thêm các link báo cáo khác --}}
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>