
import * as faceapi from 'face-api.js';

document.addEventListener('DOMContentLoaded', () => {
    // --- CẤU HÌNH ---
    const MODEL_URL = '/models';
    const API_BASE_URL = '/api/kiosk';
    const MATCH_THRESHOLD = 0.5;
    const SSD_MOBILENETV1_OPTIONS = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });
    const RECOGNITION_COOLDOWN = 5000;

    // --- DOM Elements ---
    const video = document.getElementById('video');
    const videoContainer = document.getElementById('video-container');
    const statusEl = document.getElementById('status');

    if (!video || !videoContainer || !statusEl) {
        console.error("Lỗi: Không tìm thấy các thành phần HTML cần thiết.");
        if (statusEl) logStatus('Lỗi giao diện.', true);
        return;
    }

    // --- State ---
    let faceMatcher = null;
    let otpToken = null;
    let isProcessing = false;

    /**
     * Hàm chính, điểm bắt đầu của toàn bộ logic.
     */
    async function main() {
        logStatus('Đang khởi tạo...');
        otpToken = getTokenFromUrl();
        if (!otpToken) {
            logStatus('Lỗi: Phiên không hợp lệ. Vui lòng khởi động lại từ ứng dụng Desktop.', true);
            return;
        }

        try {
            logStatus('Đang tải model AI...');
            await loadModels();

            logStatus('Đang tải dữ liệu nhân viên...');
            const labeledFaceDescriptors = await getLabeledFaceDescriptors();
            if (!labeledFaceDescriptors || labeledFaceDescriptors.length === 0) {
                logStatus('Lỗi: Không có dữ liệu khuôn mặt nào trong hệ thống.', true);
                return;
            }
            faceMatcher = new faceapi.FaceMatcher(labeledFaceDescriptors, MATCH_THRESHOLD);

            await startWebcam();
            // Lắng nghe sự kiện này để đảm bảo video đã có kích thước trước khi chạy nhận diện.
            video.addEventListener('loadedmetadata', setupCanvasAndRunRecognition);

        } catch (error) {
            console.error("Initialization Error:", error);
            logStatus(`Lỗi khởi tạo: ${error.message}`, true);
        }
    }

    /**
     * Hàm này chỉ chạy sau khi video đã có kích thước (sự kiện 'loadedmetadata').
     * Nó chịu trách nhiệm thiết lập canvas và bắt đầu vòng lặp nhận diện.
     */
    function setupCanvasAndRunRecognition() {
        logStatus('Hệ thống đã sẵn sàng. Vui lòng nhìn vào camera.');

        const canvas = faceapi.createCanvasFromMedia(video);
        videoContainer.append(canvas);
        
        // Lấy kích thước thực tế của video, sẽ không còn là 0.
        const displaySize = { width: video.videoWidth, height: video.videoHeight };
        faceapi.matchDimensions(canvas, displaySize);

        setInterval(async () => {
            if (isProcessing) return;

            const detections = await faceapi.detectAllFaces(video, SSD_MOBILENETV1_OPTIONS)
                .withFaceLandmarks()
                .withFaceDescriptors();

            canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);

            for (const detection of detections) {
                const resizedDetections = faceapi.resizeResults(detection, displaySize);
                const bestMatch = faceMatcher.findBestMatch(resizedDetections.descriptor);
                
                console.log(`Kết quả so khớp: Nhãn = ${bestMatch.label}, Khoảng cách = ${bestMatch.distance}`);
                
                const box = resizedDetections.detection.box;
                let label = bestMatch.toString();
                if (bestMatch.label !== 'unknown') {
                    const personData = faceMatcher.labeledDescriptors.find(d => d.label === bestMatch.label);
                    if (personData) {
                        label = `${personData._label} (${bestMatch.distance.toFixed(2)})`;
                    }
                }
                const drawBox = new faceapi.draw.DrawBox(box, { label: label });
                drawBox.draw(canvas);

                if (bestMatch.label !== 'unknown' && !isProcessing) {
                    isProcessing = true;
                    const userId = bestMatch.label;
                    await recordAttendance(userId, otpToken);
                    setTimeout(() => {
                        isProcessing = false;
                        logStatus('Hệ thống đã sẵn sàng. Vui lòng nhìn vào camera.');
                    }, RECOGNITION_COOLDOWN);
                }
            }
        }, 500);
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
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        video.srcObject = stream;
    }

    async function getLabeledFaceDescriptors() {
        try {
            const response = await fetch(`${API_BASE_URL}/face-descriptors`);
            if (!response.ok) throw new Error(`Server error: ${response.statusText}`);
            const data = await response.json();
            return data.map(personData => {
                const descriptors = personData.descriptors.map(d => new Float32Array(d));
                return new faceapi.LabeledFaceDescriptors(personData.label, descriptors);
            });
        } catch (error) {
            console.error("Failed to fetch descriptors:", error);
            logStatus(`Lỗi khi tải dữ liệu: ${error.message}`, true);
            return null;
        }
    }

    async function recordAttendance(userId, otpToken) {
        logStatus('Đã nhận diện. Đang gửi dữ liệu chấm công...');
        try {
            const response = await fetch(`${API_BASE_URL}/record-attendance`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ user_id: userId, otp_token: otpToken }),
            });
            const result = await response.json();
            if (response.ok) {
                logStatus(result.message);
            } else {
                logStatus(`Lỗi: ${result.message || 'Không thể chấm công.'}`, true);
            }
        } catch (error) {
            console.error("Error recording attendance:", error);
            logStatus('Lỗi kết nối đến server chấm công.', true);
        }
    }

    // --- KHỞI CHẠY ---
    main();
});