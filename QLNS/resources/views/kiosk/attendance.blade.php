<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Kiosk Chấm Công</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background: linear-gradient(135deg, #2c3e50, #4ca1af);
            color: white;
            font-family: 'Roboto', sans-serif;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        #video-container {
            position: relative; 
            width: 720px;
            height: 560px;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
            background-color: #000;
        }
        video, canvas {
            position: absolute; 
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        #status-wrapper {
            margin-top: 30px;
            padding: 15px 30px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            min-height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #status {
            font-size: 1.8em;
            font-weight: 500;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            transition: color 0.3s ease;
        }
        .status-ok { color: #2ecc71; }
        .status-error { color: #e74c3c; }
    </style>
    {{-- Token sẽ được đọc bởi kiosk_attendance.js trước, KHÔNG xóa URL ở đây --}}
</head>
<body>
    <div id="video-container">
        <video id="video" autoplay muted playsinline></video>
    </div>
    <div id="status-wrapper">
        <div id="status" class="status-ok">Đang khởi tạo...</div>
    </div>
    
    @vite('resources/js/kiosk_attendance.js')

    {{-- Chạy SAU khi kiosk_attendance.js đã đọc xong token từ URL --}}
    <script>
        // Xóa token khỏi URL sau khi trang đã load xong để tránh lộ qua Browser History
        window.addEventListener('load', function() {
            if (window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
</body>
</html>