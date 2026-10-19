<?php
return [
    'allowed_office_ips' => explode(',', env('ALLOWED_OFFICE_IPS', '14.191.112.123,127.0.0.1,192.168.65.1,192.168.23.161,192.168.23.1,192.168.1.88,192.168.1.5,192.168.1.94,192.168.1.171,172.20.10.3,192.168.101.37,::1')),

    'allowed_office_ssids' => explode(',', env('ALLOWED_OFFICE_SSIDS', 'PhongTro,BichCam,VKU-SinhVien,helu,Candles Cafe,NGUYEN THUC,Mai Cafe,Cafe Bentley,KhacDuy,DUNE 45 Trong Nha_5G,<redacted>')),

    // Cấu hình Kiosk chấm công Face ID
    'kiosk' => [
        // Thời gian sống của token phiên Kiosk (phút)
        'token_ttl_minutes' => (int) env('KIOSK_TOKEN_TTL_MINUTES', 120),
        // Khoảng cách Euclid tối đa giữa 2 vector để coi là cùng một người (càng nhỏ càng khắt khe)
        'face_match_threshold' => (float) env('KIOSK_FACE_MATCH_THRESHOLD', 0.5),
        // Sau khi chấm, bỏ qua các lần quét lại của cùng người trong khoảng này (giây)
        'repeat_cooldown_seconds' => (int) env('KIOSK_REPEAT_COOLDOWN_SECONDS', 120),
    ],
];
