<x-employee-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 md:p-8">
                
                <div class="text-center">
                    <h2 class="text-2xl font-bold text-gray-800">Đăng ký khuôn mặt</h2>
                    <p class="mt-2 text-gray-600">Để đảm bảo an toàn và chính xác, vui lòng làm theo các bước hướng dẫn.</p>
                </div>

                <div id="existing-face-status" class="mt-6 text-center p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg" style="display: none;">
                    <p>✅ Bạn đã đăng ký khuôn mặt. Bạn có muốn đăng ký lại không?</p>
                </div>
                
                <div class="mt-8 flex flex-col items-center">
                    <div id="liveness-container" class="relative w-96 h-96 bg-gray-100 rounded-full overflow-hidden border-4 border-gray-300">
                        {{-- Vùng hiển thị avatar --}}
                        <img id="avatar-preview" src="{{ asset('image/user.png') }}" alt="Avatar Preview" class="w-full h-full object-cover z-10 transition-opacity duration-300">
                        
                        {{-- Video sẽ nằm đè lên khi bắt đầu --}}
                        <video id="video" class="absolute top-0 left-0 w-full h-full object-cover z-20" autoplay muted playsinline style="display: none;"></video>
                        <canvas id="canvas" class="absolute top-0 left-0 w-full h-full z-30"></canvas>
                    </div>
                    
                    <div id="status-container" class="mt-4 h-16 text-center">
                        <h3 id="instruction" class="text-xl font-semibold text-blue-600">Đang khởi tạo...</h3>
                        <p id="feedback" class="text-gray-500 mt-1"></p>
                    </div>

                    <button id="startBtn" class="px-8 py-3 bg-blue-600 text-white font-semibold rounded-lg shadow-md hover:bg-blue-700 disabled:bg-gray-400">
                        Bắt đầu
                    </button>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        @vite(['resources/js/liveness_detection.js'])
    @endpush
</x-employee-layout>