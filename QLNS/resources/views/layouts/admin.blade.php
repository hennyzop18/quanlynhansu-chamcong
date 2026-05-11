<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Styles -->
    <!-- @livewireStyles -->
</head>

<body class="font-sans antialiased" data-success="{{ session('success') }}" data-error="{{ session('error') }}">


    <div class="min-h-screen bg-gray-100">
        {{-- Navigation Menu từ Breeze/Jetstream --}}
        <!-- @livewire('navigation-menu') -->


        <!-- Page Content -->
        <main class="flex flex-col md:flex-row min-h-screen">
            {{-- Mobile Header --}}
            <div class="md:hidden sticky top-0 z-50 bg-white shadow-sm p-4 flex justify-between items-center border-b">
                <span class="font-bold text-gray-700">QLNS Admin</span>
                <button id="mobile-menu-button" class="text-gray-600 focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            {{-- Admin Sidebar --}}
            <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-md transform -translate-x-full md:sticky md:top-0 md:h-screen md:overflow-y-auto md:translate-x-0 transition-transform duration-300 ease-in-out">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">Quản lý</h3>
                    <nav class="space-y-2">
                        {{-- Link Dashboard (nếu có view riêng) --}}
                        {{-- <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.dashboard') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">📊</span> Dashboard
                        </a> --}}
                        <a href="{{ route('admin.employees.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.employees.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">👥</span> Nhân sự
                        </a>
                        <a href="{{ route('admin.positions.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.positions.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">🏷️</span> Chức vụ
                        </a>
                        <a href="{{ route('admin.departments.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.departments.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">🏢</span> Phòng ban
                        </a>
                        <a href="{{ route('admin.work-schedules.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.work-schedules.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">⏰</span> Ca làm việc
                        </a>
                        <a href="{{ route('admin.schedule_approvals.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.schedule_approvals.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">✔️</span> Duyệt Lịch Đăng Ký
                        </a>
                        <a href="{{ route('admin.attendances.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.attendances.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">✅</span> Chấm công
                        </a>
                        <a href="{{ route('admin.salaries.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.salaries.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">💰</span> Quản lý lương
                        </a>
                        <a href="{{ route('admin.rewards-discipline.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.rewards-discipline.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">🏆</span> Khen thưởng/Kỷ luật
                        </a>
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">👤</span> Tài khoản
                        </a>
                        <a href="{{ route('admin.reports.index') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md {{ request()->routeIs('admin.reports.*') ? 'bg-gray-200 font-semibold' : '' }}">
                            <span class="mr-2">📈</span> Thống kê
                        </a>
                        <a href="{{ route('logout') }}"
                            class="flex items-center px-3 py-2 text-gray-600 hover:bg-gray-200 hover:text-gray-700 rounded-md"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <span class="mr-2">🚪</span> Đăng xuất
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                            @csrf
                        </form>
                        {{-- Thêm các link khác --}}
                    </nav>
                </div>
            </aside>

            {{-- Main Content Area --}}
            <div class="flex-1">
                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-white shadow sticky top-[57px] md:top-0 z-40">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                {{-- Content từ các view con --}}
                {{ $slot }}
            </div>
        </main>
    </div>

    @stack('modals')
    <!-- @livewireScripts -->
    {{-- Thêm script riêng nếu cần --}}
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cấu hình Toast mặc định
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Hiển thị thông báo từ Session Flash (qua data-attributes)
            const successMsg = document.body.dataset.success;
            const errorMsg = document.body.dataset.error;

            if (successMsg && successMsg !== "") {
                Toast.fire({
                    icon: 'success',
                    title: successMsg
                });
            }

            if (errorMsg && errorMsg !== "") {
                Toast.fire({
                    icon: 'error',
                    title: errorMsg
                });
            }

            // Tự động xử lý các form có class 'confirm-form'
            document.body.addEventListener('submit', function(e) {
                const form = e.target.closest('.confirm-form');
                if (form && !form.dataset.confirmed) {
                    e.preventDefault();
                    const title = form.getAttribute('data-title') || 'Xác nhận?';
                    const text = form.getAttribute('data-text') || 'Hành động này không thể hoàn tác.';
                    const icon = form.getAttribute('data-icon') || 'question';
                    const confirmBtnText = form.getAttribute('data-confirm-text') || 'Đồng ý';
                    
                    Swal.fire({
                        title: title,
                        text: text,
                        icon: icon,
                        showCancelButton: true,
                        confirmButtonColor: '#4f46e5',
                        cancelButtonColor: '#ef4444',
                        confirmButtonText: confirmBtnText,
                        cancelButtonText: 'Hủy'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.dataset.confirmed = "true";
                            form.submit();
                        }
                    });
                }
            });
        });

        // Mobile Menu Toggle
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const mobileMenuButton = document.getElementById('mobile-menu-button');

        if (mobileMenuButton) {
            mobileMenuButton.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            });
        }

        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            });
        }
    </script>
    {{-- Overlay for Mobile Sidebar --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black opacity-50 z-40 hidden md:hidden"></div>
</body>

</html>