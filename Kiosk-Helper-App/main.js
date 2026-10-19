const { app, BrowserWindow, ipcMain } = require('electron');
const path = require('path');
const { exec } = require('child_process');
const wifi = require('node-wifi');
const { parseWindowsSsid } = require('./wifi-windows');

// node-wifi chỉ dùng cho Linux. macOS và Windows tự đọc bằng lệnh hệ thống (xem getSsid).
wifi.init({
    iface: null // Tự động nhận diện Network Interface mặc định
});
const axios = require('axios');
const open = (...args) => import('open').then(({ default: open }) => open(...args));

// --- CẤU HÌNH ---
// Địa chỉ backend Laravel. Có thể đổi bằng biến môi trường QLNS_SERVER_URL,
// ví dụ: set QLNS_SERVER_URL=https://ten-space.hf.space
// Dùng 127.0.0.1 thay vì localhost để không bị lệch sang IPv6 (::1) trên Windows.
const SERVER_URL = (process.env.QLNS_SERVER_URL || 'http://127.0.0.1:8000').replace(/\/+$/, '');
const LARAVEL_API_URL = `${SERVER_URL}/api/kiosk/request-token`;
const KIOSK_WEB_URL = `${SERVER_URL}/kiosk/attendance`;
// --- KẾT THÚC CẤU HÌNH ---


function createWindow() {
    const mainWindow = new BrowserWindow({
        width: 500,
        height: 350,
        resizable: false,
        webPreferences: {
            preload: path.join(__dirname, 'preload.js'),
            contextIsolation: true, // Bảo mật
            nodeIntegration: false,
        }
    });

    mainWindow.loadFile('index.html');
    // mainWindow.webContents.openDevTools(); // Bỏ comment để debug
}

const getSsid = () => new Promise((resolve, reject) => {
    const platform = process.platform;

    if (platform === 'darwin') {
        // ===== macOS: Dùng system_profiler (airport đã bị Apple xóa trên macOS Ventura/Sonoma) =====
        const command = `system_profiler SPAirPortDataType | awk '/Current Network Information:/ { getline; sub(/^[ \\t]*/, ""); sub(/:$/, ""); print; exit }'`;
        exec(command, (error, stdout, stderr) => {
            if (error) {
                return reject(new Error(`Lỗi macOS system_profiler: ${stderr || error.message}`));
            }
            const ssid = stdout.trim();
            if (ssid) {
                resolve(ssid);
            } else {
                reject(new Error('Không lấy được SSID. Vui lòng đảm bảo đang kết nối Wi-Fi.'));
            }
        });
    } else if (platform === 'win32') {
        // ===== Windows 10/11: tự gọi netsh và tìm dòng SSID =====
        // node-wifi đọc netsh theo số thứ tự dòng cố định nên bị lệch trên Windows 11.
        // chcp 65001 để tên Wi-Fi có dấu tiếng Việt không bị lỗi font.
        exec('chcp 65001 >nul & netsh wlan show interfaces', { windowsHide: true, encoding: 'utf8' }, (error, stdout, stderr) => {
            const output = `${stdout || ''}\n${stderr || ''}`;

            // Windows 11 24H2+: netsh chỉ trả SSID khi đã bật quyền Location
            if (/privacy-location|location permission/i.test(output)) {
                exec('start ms-settings:privacy-location', { windowsHide: true });
                return reject(new Error('Windows đang chặn đọc tên Wi-Fi. Trong cửa sổ vừa mở, bật "Location services" và "Let desktop apps access your location", rồi bấm Thử lại.'));
            }

            const ssid = parseWindowsSsid(output);
            if (ssid) {
                return resolve(ssid);
            }
            if (/wlansvc|is not running|không chạy/i.test(output)) {
                return reject(new Error('Dịch vụ Wi-Fi (WLAN AutoConfig) chưa chạy hoặc máy không có card Wi-Fi.'));
            }
            reject(new Error('Không tìm thấy kết nối Wi-Fi nào. Hãy kết nối Wi-Fi văn phòng (máy cắm dây LAN sẽ không có SSID).'));
        });
    } else {
        // ===== Linux: Dùng node-wifi =====
        wifi.getCurrentConnections((error, currentConnections) => {
            if (error) {
                return reject(new Error(`Lỗi khi lấy thông tin Wi-Fi: ${error.message}`));
            }
            if (currentConnections && currentConnections.length > 0) {
                const ssid = currentConnections[0].ssid || currentConnections[0].name;
                if (ssid) {
                    resolve(ssid);
                } else {
                    reject(new Error('Đang bắt được mạng nhưng không lấy được tên (SSID).'));
                }
            } else {
                reject(new Error('Không tìm thấy kết nối Wi-Fi nào. Hãy kiểm tra lại kết nối mạng.'));
            }
        });
    }
});

// Lắng nghe sự kiện 'start-check-in' từ giao diện
ipcMain.handle('start-check-in', async () => {
    try {
        const currentSSID = await getSsid();

        const response = await axios.post(LARAVEL_API_URL, { ssid: currentSSID }, { timeout: 15000 });

        const { otp_token } = response.data;

        if (otp_token) {
            await open(`${KIOSK_WEB_URL}?token=${otp_token}`);
            setTimeout(() => app.quit(), 500);
            return { success: true, message: 'Thành công! Đang mở Kiosk...' };
        } else {
            return { success: false, message: 'Lỗi: Server không trả về token.' };
        }

    } catch (error) {
        console.error('Error in start-check-in:', error);
        if (error.code === 'ECONNREFUSED' || error.code === 'ECONNABORTED') {
            return { success: false, message: `Thất bại: Không kết nối được server ${SERVER_URL}. Hãy chạy start-windows.bat trước.` };
        }
        const errorMessage = error.response?.data?.message || error.message || 'Lỗi không xác định.';
        return { success: false, message: `Thất bại: ${errorMessage}` };
    }
});

app.whenReady().then(() => {
    createWindow();
    app.on('activate', () => {
        if (BrowserWindow.getAllWindows().length === 0) createWindow();
    });
});

app.on('window-all-closed', () => {
    if (process.platform !== 'darwin') app.quit();
});

