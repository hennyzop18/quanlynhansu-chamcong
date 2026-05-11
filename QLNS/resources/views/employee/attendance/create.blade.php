<x-employee-layout> {{-- Hoặc dùng layout employee nếu có --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chấm công hôm nay') }} ({{ \Carbon\Carbon::today()->format('d/m/Y') }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 text-center">

                    {{-- Thông báo --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-200 rounded-md">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded-md">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Hiển thị trạng thái và nút bấm --}}
                    @if (!$checkedIn)
                        {{-- Chưa Check-in --}}
                        <form action="{{ route('employee.attendance.checkin') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="px-6 py-3 bg-green-500 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-400 focus:ring-opacity-75 text-lg">
                                Check In
                            </button>
                        </form>
                    @elseif (!$checkedOut)
                        {{-- Đã Check-in, chưa Check-out --}}
                        <p class="mb-4 text-gray-700">Bạn đã Check-in lúc: <span
                                class="font-semibold">{{ $todaysAttendance->check_in_time->format('H:i:s') }}</span></p>
                        <form action="{{ route('employee.attendance.checkout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="px-6 py-3 bg-red-500 text-white font-semibold rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-400 focus:ring-opacity-75 text-lg">
                                Check Out
                            </button>
                        </form>
                    @else
                        {{-- Đã Check-in và Check-out --}}
                        <p class="mb-2 text-gray-700">Check-in: <span
                                class="font-semibold">{{ $todaysAttendance->check_in_time->format('H:i:s') }}</span></p>
                        <p class="mb-4 text-gray-700">Check-out: <span
                                class="font-semibold">{{ $todaysAttendance->check_out_time->format('H:i:s') }}</span></p>
                        <p class="text-green-600 font-semibold">Bạn đã hoàn thành chấm công hôm nay.</p>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('employee.attendance.history') }}" class="text-blue-500 hover:underline">Xem
                            lịch sử chấm công</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-employee-layout>