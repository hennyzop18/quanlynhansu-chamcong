<?php
return [
    'allowed_office_ips' => explode(',', env('ALLOWED_OFFICE_IPS', '14.191.112.123,127.0.0.1,192.168.65.1,192.168.23.161,192.168.23.1,192.168.1.88,192.168.1.5,192.168.1.94,192.168.1.171,172.20.10.3')),

    'allowed_office_ssids' => explode(',', env('ALLOWED_OFFICE_SSIDS', 'PhongTro,BichCam,VKU-SinhVien,helu,Candles Cafe,NGUYEN THUC,Mai Cafe,Cafe Bentley,KhacDuy')),
];
