import * as faceapi from 'face-api.js';

const video = document.getElementById('video');
const registerBtn = document.getElementById('registerBtn');
const statusEl = document.getElementById('status');
const MODEL_URL = '/models';

function getCsrfToken() {
    const tokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (tokenMeta) {
        return tokenMeta.getAttribute('content');
    }
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
    return null;
}


async function setupCamera() {
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
    } catch (err) {
        console.error(err);
        statusEl.textContent = 'Lỗi: Không thể truy cập camera.';
    }
}

async function loadModels() {
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
        faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
        faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
    ]);
    statusEl.textContent = 'Model đã sẵn sàng.';
    registerBtn.textContent = 'Chụp và Lưu';
    registerBtn.disabled = false;
}

registerBtn.addEventListener('click', async () => {
    statusEl.textContent = 'Đang xử lý...';
    registerBtn.disabled = true;

    const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        statusEl.textContent = 'Không tìm thấy khuôn mặt. Vui lòng thử lại.';
        registerBtn.disabled = false;
        return;
    }

    const csrfToken = getCsrfToken(); // <-- Lấy token
    if (!csrfToken) {
        statusEl.textContent = 'Lỗi bảo mật: Không tìm thấy CSRF Token.';
        registerBtn.disabled = false;
        return;
    }

    try {
        const response = await fetch('/employee/face/register', { // <-- Sửa lại URL
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken // <-- GỬI TOKEN TRONG HEADER
            },
            body: JSON.stringify({ descriptor: JSON.stringify(Array.from(detection.descriptor)) })
        });
        
        const data = await response.json();
        
        if (response.ok) {
            statusEl.textContent = data.message;
        } else {
            // Hiển thị lỗi từ server, ví dụ như lỗi validation
            statusEl.textContent = `Lỗi (${response.status}): ${data.message || 'Vui lòng thử lại.'}`;
        }
    } catch (error) {
        statusEl.textContent = 'Lỗi kết nối đến server.';
    } finally {
        registerBtn.disabled = false;
    }
});

// Chạy
setupCamera();
loadModels();