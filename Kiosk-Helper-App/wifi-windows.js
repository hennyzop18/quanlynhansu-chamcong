/**
 * Lấy SSID từ kết quả `netsh wlan show interfaces` (Windows).
 * Dòng cần tìm có dạng "    SSID                   : Ten wifi".
 * Không khớp "AP BSSID"/"BSSID" vì phải bắt đầu đúng bằng chữ SSID.
 * Nhãn "SSID" giữ nguyên trên Windows tiếng Việt lẫn tiếng Anh.
 */
function parseWindowsSsid(output) {
    const match = String(output || '').match(/^\s*SSID\s*:\s*(.+?)\s*$/m);
    return match ? match[1] : null;
}

module.exports = { parseWindowsSsid };
