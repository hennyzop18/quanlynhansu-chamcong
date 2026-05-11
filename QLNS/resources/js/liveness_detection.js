import * as faceapi from 'face-api.js';
document.addEventListener('DOMContentLoaded', () => {
    const MODEL_URL = '/models';
    // Sử dụng model SsdMobilenetv1 mạnh mẽ để có kết quả tốt nhất
    const SSD_MOBILENETV1_OPTIONS = new faceapi.SsdMobilenetv1Options({ minConfidence: 0.5 });
    const HEAD_TURN_THRESHOLD = 15; // Góc quay đầu yêu cầu (độ)
    
    // --- DOM Elements ---
    const videoEl = document.getElementById('video');
    const instructionEl = document.getElementById('instruction');
    const feedbackEl = document.getElementById('feedback');
    const startBtn = document.getElementById('startBtn');
    const existingFaceStatusEl = document.getElementById('existing-face-status');
    const avatarPreviewEl = document.getElementById('avatar-preview'); // Ảnh đại diện hiện tại

    if (!videoEl || !instructionEl || !feedbackEl || !startBtn) {
        console.error("Lỗi khởi tạo: Một hoặc nhiều phần tử HTML không được tìm thấy.");
        return;
    }

    // --- Trạng thái & Thử thách lắc đầu ---
    let modelsLoaded = false;
    let capturedFaceCanvas = null; // Sẽ lưu lại ảnh canvas của khuôn mặt lúc nhìn thẳng
    let detectionInterval = null;
    const CHALLENGES = { LOOK_STRAIGHT: 'LOOK_STRAIGHT', TURN_LEFT: 'TURN_LEFT', TURN_RIGHT: 'TURN_RIGHT' };
    const challengeSequence = [CHALLENGES.LOOK_STRAIGHT, CHALLENGES.TURN_LEFT, CHALLENGES.TURN_RIGHT];
    let currentChallengeIndex = 0;
    /**
     * Tải tất cả các model AI cần thiết.
     */
    async function initialize() {
        instructionEl.textContent = 'Đang kiểm tra trạng thái...';
        startBtn.disabled = true;
        
        await checkExistingFace();
        instructionEl.textContent = 'Đang tải model AI...';
        try {
            console.log('Bắt đầu tải các model...');
            await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            console.log('Tất cả model đã được tải thành công!');
            modelsLoaded = true;
            instructionEl.textContent = 'Nhấn "Bắt đầu" khi bạn đã sẵn sàng';
            startBtn.disabled = false;
        } catch (error) {
            console.error('LỖI KHI TẢI MODEL:', error);
            instructionEl.textContent = 'Lỗi khi tải model AI. Vui lòng kiểm tra Console.';
            feedbackEl.textContent = error.message;
        }
    }

    /**
     * Gọi API để kiểm tra xem người dùng đã có dữ liệu khuôn mặt chưa
     */
    async function checkExistingFace() {
        try {
            const response = await fetch('/employee/face/status');
            const data = await response.json();
            
            if (data.avatar_url) {
                avatarPreviewEl.src = data.avatar_url;
            }

            if (data.has_face_data) {
                // Nếu đã có, hiển thị thông báo
                
                existingFaceStatusEl.style.display = 'block';
                instructionEl.style.display = 'none';
                startBtn.textContent = 'Đăng Ký Lại'; // Đổi chữ trên nút
            } else {
                existingFaceStatusEl.style.display = 'none';
                startBtn.textContent = 'Bắt đầu';
            }
        } catch (error) {
            console.error("Lỗi khi kiểm tra trạng thái khuôn mặt:", error);
            // Bỏ qua lỗi và tiếp tục
        }
    }


    /**
     * Bắt đầu quy trình xác thực liveness.
     */
    async function startLivenessDetection() {
        if (existingFaceStatusEl.style.display === 'block') {
            const isConfirmed = confirm('Hành động này sẽ xóa tất cả các phiên bản khuôn mặt đã đăng ký trước đó và bắt đầu đăng ký mới. Bạn có chắc chắn muốn tiếp tục?');
            if (!isConfirmed) {
                return; // Người dùng hủy, không làm gì cả
            }
        }
        avatarPreviewEl.style.display = 'none';
        videoEl.style.display = 'block';

        startBtn.style.display = 'none';
        currentChallengeIndex = 0;
        capturedFaceCanvas = null;
        feedbackEl.textContent = '';
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: true });
            videoEl.srcObject = stream;
        } catch (err) {
            instructionEl.textContent = 'Lỗi: Không thể truy cập camera.';
            startBtn.style.display = 'block';
            return;
        }

        videoEl.addEventListener('play', () => {
            if (detectionInterval) clearInterval(detectionInterval);
            detectionInterval = setInterval(runDetectionLoop, 500);
        });
    }

    /**
     * Vòng lặp chính để phát hiện và kiểm tra thử thách.
     */
    async function runDetectionLoop() {
        if (currentChallengeIndex >= challengeSequence.length) {
            await completeLivenessCheck();
            return; // Dừng vòng lặp khi đã hoàn tất
        }
        
        showInstruction(challengeSequence[currentChallengeIndex]);
        
        // Chỉ cần landmarks để tính góc quay đầu, không cần descriptor ở bước này
        const detection = await faceapi.detectSingleFace(videoEl, SSD_MOBILENETV1_OPTIONS)
            .withFaceLandmarks();

        if (detection) {
            const headAngle = getHeadAngle(detection.landmarks);
            let challengePassed = false;
            
            switch (challengeSequence[currentChallengeIndex]) {
                case CHALLENGES.LOOK_STRAIGHT:
                    // if (Math.abs(headAngle.yaw) < 5) {
                    //     feedbackEl.textContent = 'Đã nhận diện khuôn mặt chính diện!';
                    //     // Trích xuất ảnh canvas của khuôn mặt tại thời điểm này
                    //     const faces = await faceapi.extractFaces(videoEl, [detection.detection]);
                    //     if (faces.length > 0) {
                    //         capturedFaceCanvas = faces[0];
                    //     }
                    //     challengePassed = true;
                    // } else {
                    //     feedbackEl.textContent = 'Vui lòng nhìn thẳng vào camera.';
                    // }
                    // break;
                    if (Math.abs(headAngle.yaw) < 5) {
                    feedbackEl.textContent = 'Đã nhận diện khuôn mặt chính diện!';
                    
                    // === THAY ĐỔI LOGIC CHỤP ẢNH TẠI ĐÂY ===
                    // 1. Lấy khung gốc từ kết quả phát hiện
                    const box = detection.detection.box;

                    // 2. Phóng to khung đó ra để lấy vùng ảnh rộng hơn
                    const paddedBox = getPaddedBox(box);

                    // 3. Trích xuất ảnh từ video dựa trên khung đã được phóng to
                    const faces = await faceapi.extractFaces(videoEl, [paddedBox]);
                    // =======================================
                    
                    if (faces.length > 0) {
                        capturedFaceCanvas = faces[0];
                    }
                    challengePassed = true;
                } else {
                    feedbackEl.textContent = 'Vui lòng nhìn thẳng vào camera.';
                }
                break;
                case CHALLENGES.TURN_LEFT:
                    if (headAngle.yaw > HEAD_TURN_THRESHOLD) {
                        feedbackEl.textContent = 'Tốt!';
                        challengePassed = true;
                    } else {
                        feedbackEl.textContent = 'Hãy quay đầu sang TRÁI nhiều hơn.';
                    }
                    break;
                case CHALLENGES.TURN_RIGHT:
                    if (headAngle.yaw < -HEAD_TURN_THRESHOLD) {
                        feedbackEl.textContent = 'Tuyệt vời!';
                        challengePassed = true;
                    } else {
                        feedbackEl.textContent = 'Hãy quay đầu sang PHẢI nhiều hơn.';
                    }
                    break;
            }

            if (challengePassed) {
                currentChallengeIndex++;
            }
        } else {
            feedbackEl.textContent = 'Không tìm thấy khuôn mặt. Vui lòng giữ ổn định.';
        }
    }

    /**
 * Hàm mới: Phóng to một Bounding Box
 * @param {Box} box - Khung gốc
 * @returns {Box} - Khung đã được phóng to
 */
function getPaddedBox(box) {
    // Tỷ lệ phóng to, ví dụ 1.5 = phóng to 50%
    const padding = 2.5; 
    
    const { x, y, width, height } = box;
    
    // Tính toán kích thước mới
    const newWidth = width * padding;
    const newHeight = height * padding;
    
    // Tính toán tọa độ mới để khuôn mặt vẫn ở trung tâm
    const newX = x - (newWidth - width) / 2;
    const newY = y - (newHeight - height) / 2;

    // Trả về một đối tượng Box mới
    return new faceapi.Rect(newX, newY, newWidth, newHeight);
}

    /**
     * Hoàn thành, tính toán descriptor từ ảnh tĩnh và gửi đi.
     */
    async function completeLivenessCheck() {
        if (detectionInterval) clearInterval(detectionInterval);
        
        instructionEl.textContent = 'Xác thực thành công!';
        if (videoEl.srcObject) videoEl.srcObject.getTracks().forEach(track => track.stop());

        if (!capturedFaceCanvas) {
            instructionEl.textContent = 'Đăng ký thất bại: Không chụp được ảnh chính diện.';
            startBtn.style.display = 'block';
            return;
        }
        
        feedbackEl.textContent = 'Đang tạo dữ liệu khuôn mặt để đăng ký...';
        
        // // Tính toán descriptor từ ảnh canvas tĩnh đã chụp
        // const descriptor = await faceapi.computeFaceDescriptor(capturedFaceCanvas);
        //  // Lấy ảnh canvas dưới dạng data URL (base64)
        // const avatarBase64 = capturedFaceCanvas.toDataURL('image/jpeg', 0.9);

         // === THAY ĐỔI CÁCH TÍNH DESCRIPTOR ===
        // 1. Phát hiện lại khuôn mặt bên trong ảnh canvas đã được cắt
        const detectionInCanvas = await faceapi.detectSingleFace(capturedFaceCanvas, SSD_MOBILENETV1_OPTIONS)
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detectionInCanvas) {
            instructionEl.textContent = 'Lỗi: Không thể xử lý ảnh đã chụp.';
            startBtn.style.display = 'block';
            return;
        }
    
    // 2. Lấy descriptor đã được tính toán chính xác
    const descriptor = detectionInCanvas.descriptor;
    // ======================================

    // Lấy ảnh canvas dưới dạng data URL (base64)
    const avatarBase64 = capturedFaceCanvas.toDataURL('image/jpeg', 0.9);
    

        if (descriptor && avatarBase64) {
            await saveDescriptorAndAvatar(descriptor, avatarBase64);
        } else {
            instructionEl.textContent = 'Lỗi: Không thể tạo dữ liệu khuôn mặt.';
            startBtn.style.display = 'block';
        }
    }
    /**
     * Hiển thị chỉ dẫn cho thử thách
     * @param {string} challenge 
     */
   function showInstruction(challenge) {
        switch (challenge) {
            case CHALLENGES.LOOK_STRAIGHT:
                instructionEl.textContent = 'Bước 1: Giữ yên và nhìn thẳng';
                break;
            case CHALLENGES.TURN_LEFT:
                instructionEl.textContent = 'Bước 2: Từ từ quay đầu sang TRÁI';
                break;
            case CHALLENGES.TURN_RIGHT:
                instructionEl.textContent = 'Bước 3: Từ từ quay đầu sang PHẢI';
                break;
        }
    }
    
    function getHeadAngle(landmarks) {
        const nose = landmarks.getNose()[3];
        const jawLeft = landmarks.getJawOutline()[0];
        const jawRight = landmarks.getJawOutline()[16];
        const distToLeftJaw = Math.hypot(nose.x - jawLeft.x, nose.y - jawLeft.y);
        const distToRightJaw = Math.hypot(nose.x - jawRight.x, nose.y - jawRight.y);
        const ratio = distToLeftJaw / distToRightJaw;
        return { yaw: (1 - ratio) * 100 };
    }


    async function saveDescriptorAndAvatar(descriptor, avatar) {
        feedbackEl.textContent = 'Đang gửi dữ liệu đến server...';
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const response = await fetch('/employee/face/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    descriptor: JSON.stringify(Array.from(descriptor)),
                    avatar: avatar
                 })
            });
            const data = await response.json();
            if (response.ok) {
                instructionEl.textContent = 'ĐĂNG KÝ THÀNH CÔNG!';
                feedbackEl.textContent = data.message;
                if(data.new_avatar_url) {
                    avatarPreviewEl.src = data.new_avatar_url;
                }
                 await checkExistingFace();
            } else {
                instructionEl.textContent = 'Đăng ký thất bại.';
                feedbackEl.textContent = `Lỗi: ${data.message}`;
            }
        } catch (error) {
            instructionEl.textContent = 'Lỗi kết nối.';
            feedbackEl.textContent = error.message;
        } finally {
            startBtn.style.display = 'block';
            videoEl.style.display = 'none'; // Ẩn video
            avatarPreviewEl.style.display = 'block'; // Hiện lại ảnh
        }
    }

    // --- Khởi chạy ---
    startBtn.addEventListener('click', startLivenessDetection);
    initialize();

});
