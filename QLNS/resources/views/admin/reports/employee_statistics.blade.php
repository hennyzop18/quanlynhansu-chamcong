<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Thống kê Nhân sự') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Thống kê theo phòng ban --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Theo Phòng Ban</h3>
                    <p class="mb-4 text-sm text-gray-600">Tổng số nhân viên active: {{ $totalEmployees }}</p>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Phòng Ban</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Số Lượng</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($statsByDepartment as $stat)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $stat->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->employees_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center p-4 text-gray-500">Chưa có dữ liệu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- Có thể thêm biểu đồ ở đây dùng Chart.js --}}
                    {{-- <canvas id="departmentChart"></canvas> --}}
                </div>
            </div>

            {{-- Thống kê theo chức vụ --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Theo Chức Vụ</h3>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Chức Vụ</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Số Lượng</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($statsByPosition as $stat)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $stat->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->employees_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="text-center p-4 text-gray-500">Chưa có dữ liệu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{-- <canvas id="positionChart"></canvas> --}}
                </div>
            </div>

        </div>
        {{-- Link quay lại menu báo cáo --}}
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-6">
            <a href="{{ route('admin.reports.index') }}" class="text-blue-500 hover:underline">← Quay lại Danh sách Báo
                cáo</a>
        </div>
    </div>
    {{-- @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Code JS để vẽ biểu đồ nếu cần
    </script>
    @endpush --}}
</x-admin-layout>