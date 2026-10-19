
import * as faceapi from 'face-api.js';

/**
 * Trang Kiosk chấm công Face ID.
 *
 * Luồng: đọc token phiên từ URL -> tải model AI -> bật camera -> cứ vài trăm ms phát hiện
 * khuôn mặt, tính vector 128 chiều và gửi lên server. Server tự so khớp với khuôn mặt đã
 * đăng ký và trả về tên nhân viên + kết quả check-in/check-out.
 */
document.addEventListener('DOMContentLoaded', () => {
    // --- CẤU HÌNH ---
    const MODEL_URL = '/models';
    const API_BASE_URL = '/api/kiosk';
    const SSD_MOBILENETV1_OPTIONS = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });
    const DETECT_INTERVAL_MS = 400;   // chu kỳ phát hiện khuôn mặt
    const MIN_SEND_SCORE = 0.8;       // chỉ gửi khi khuôn mặt đủ rõ
    const SEND_INTERVAL_MS = 1500;    // không gửi lên server dày hơn mức này
    const RESULT_DISPLAY_MS = 4000;   // thời gian giữ thông báo kết quả trên màn hình

    // --- DOM Elements ---
    const video = document.getElementById('video');
    const videoContainer = document.getElementById('video-container');
    const statusEl = document.getElementById('status');

    if (!video || !videoContainer || !statusEl) {
        console.error('Lỗi: Không tìm thấy các thành phần HTML cần thiết.');
        return;
    }

    // --- State ---
    let otpToken = null;
    let isSending = false;
    let sessionExpired = false;
    let lastSentAt = 0;
    let pauseUntil = 0;               // tạm dừng gửi sau khi đã chấm công thành công
    let lastLabel = '';               // nhãn vẽ trên khung mặt (tên người vừa nhận ra)
    let loopTimer = null;

    async function main() {
        logStatus('Đang khởi tạo...');
        otpToken = getTokenFromUrl();
        if (!otpToken) {
            logStatus('Lỗi: Phiên không hợp lệ. Vui lòng khởi động lại từ ứng dụng Kiosk.', true);
            return;
        }

        try {
            logStatus('Đang tải model AI...');
            await loadModels();
            await startWebcam();
            // Chờ video có kích thước thật rồi mới dựng canvas và chạy nhận diện
            if (video.readyState >= 1) {
                setupCanvasAndRunRecognition();
            } else {
                video.addEventListener('loadedmetadata', setupCanvasAndRunRecognition, { once: true });
            }
        } catch (error) {
            console.error('Initialization Error:', error);
            logStatus(`Lỗi khởi tạo: ${error.message}`, true);
        }
    }

    function setupCanvasAndRunRecognition() {
        logStatus('Hệ thống đã sẵn sàng. Vui lòng nhìn vào camera.');

        const canvas = faceapi.createCanvasFromMedia(video);
        videoContainer.append(canvas);
        const displaySize = { width: video.videoWidth, height: video.videoHeight };
        faceapi.matchDimensions(canvas, displaySize);
        const ctx = canvas.getContext('2d');

        loopTimer = setInterval(async () => {
            if (sessionExpired) return;

            const detection = await faceapi
                .detectSingleFace(video, SSD_MOBILENETV1_OPTIONS)
                .withFaceLandmarks()
                .withFaceDescriptor();

            ctx.clearRect(0, 0, canvas.width, canvas.height);
            if (!detection) {
                if (Date.now() > pauseUntil) lastLabel = '';
                return;
            }

            // Vẽ khung (video đang lật gương bằng CSS nên lật toạ độ X của khung theo)
            const resized = faceapi.resizeResults(detection, displaySize);
            const box = resized.detection.box;
            const mirroredBox = new faceapi.Rect(displaySize.width - box.x - box.width, box.y, box.width, box.height);
            new faceapi.draw.DrawBox(mirroredBox, { label: lastLabel || undefined }).draw(canvas);

            const now = Date.now();
            const canSend = !isSending
                && now >= pauseUntil
                && now - lastSentAt >= SEND_INTERVAL_MS
                && detection.detection.score >= MIN_SEND_SCORE;
            if (canSend) {
                lastSentAt = now;
                recordAttendance(Array.from(detection.descriptor));
            }
        }, DETECT_INTERVAL_MS);
    }

    async function recordAttendance(descriptor) {
        isSending = true;
        try {
            const response = await fetch(`${API_BASE_URL}/record-attendance`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ otp_token: otpToken, descriptor }),
            });
            const result = await response.json().catch(() => ({}));

            if (response.ok) {
                // 200: đã chấm công, hoặc người này vừa chấm xong (duplicate)
                lastLabel = result.name || '';
                logStatus(result.message, false);
                pauseUntil = Date.now() + RESULT_DISPLAY_MS;
                setTimeout(() => {
                    if (!sessionExpired && Date.now() >= pauseUntil) {
                        lastLabel = '';
                        logStatus('Hệ thống đã sẵn sàng. Vui lòng nhìn vào camera.');
                    }
                }, RESULT_DISPLAY_MS);
            } else if (response.status === 404) {
                lastLabel = 'Không nhận ra';
                logStatus(result.message || 'Không nhận ra khuôn mặt.', true);
            } else if (response.status === 403) {
                sessionExpired = true;
                clearInterval(loopTimer);
                logStatus(result.message || 'Phiên chấm công đã hết hạn. Vui lòng mở lại từ ứng dụng Kiosk.', true);
            } else if (response.status === 429) {
                pauseUntil = Date.now() + 5000;
                logStatus('Quá nhiều yêu cầu, vui lòng đợi vài giây...', true);
            } else {
                logStatus(`Lỗi: ${result.message || 'Không thể chấm công.'}`, true);
            }
        } catch (error) {
            console.error('Error recording attendance:', error);
            logStatus('Lỗi kết nối đến server chấm công.', true);
        } finally {
            isSending = false;
        }
    }

    // --- Các hàm phụ ---

    function logStatus(message, isError = false) {
        statusEl.textContent = message;
        statusEl.className = isError ? 'status-error' : 'status-ok';
    }

    function getTokenFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('token');
    }

    async function loadModels() {
        await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
    }

    async function startWebcam() {
        if (!navigator.mediaDevices?.getUserMedia) {
            throw new Error('Trình duyệt không cho dùng camera. Hãy mở trang qua http://127.0.0.1 hoặc https.');
        }
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
    }

    // --- KHỞI CHẠY ---
    main();
});
