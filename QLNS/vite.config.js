import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';

// export default defineConfig({
//     plugins: [
//         laravel({
//             input: ['resources/css/app.css', 'resources/js/app.js'],
//             refresh: true,
//         }),
//     ],
// });
export default defineConfig(({ mode }) => {
    // Tải các biến môi trường
    const env = loadEnv(mode, process.cwd(), '');

    return {
        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/kiosk_attendance.js', 
                    // 'resources/js/face_register.js',
                     'resources/js/liveness_detection.js'],
                refresh: true,
            }),
        ],
        // === THÊM BLOCK CẤU HÌNH SERVER DƯỚI ĐÂY ===
        server: {
            // Lắng nghe trên tất cả các địa chỉ IP public
            host: '0.0.0.0', 
            // Cổng mặc định của Vite
            port: 5173, 
            // Cấu hình HMR (Hot Module Replacement)
            hmr: {
                // Ép HMR client sử dụng 'localhost' để kết nối
                host: 'localhost', 
            },
            // Định nghĩa origin để Vite tạo ra URL chính xác trong file `hot`
            // Sử dụng biến môi trường VITE_ORIGIN, nếu không có thì fallback
            origin: env.VITE_ORIGIN || 'http://localhost:5173',
            cors: true 
        }
    }
});