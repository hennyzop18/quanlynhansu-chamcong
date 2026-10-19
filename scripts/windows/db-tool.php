<?php
// Helper cho setup.ps1:
//   php db-tool.php create       -> tạo database nếu chưa có
//   php db-tool.php count-users  -> in số dòng bảng users (để biết có cần seed không)
// Thông tin kết nối lấy từ biến môi trường QLNS_DB_* do setup.ps1 đặt.

$host = getenv('QLNS_DB_HOST') ?: '127.0.0.1';
$port = getenv('QLNS_DB_PORT') ?: '3306';
$name = getenv('QLNS_DB_NAME') ?: 'qlns';
$user = getenv('QLNS_DB_USER') ?: 'root';
$pass = getenv('QLNS_DB_PASS');
if ($pass === false) {
    $pass = '';
}

$cmd = $argv[1] ?? 'create';
$opts = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

try {
    if ($cmd === 'create') {
        $pdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, $opts);
        $safe = str_replace('`', '``', $name);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "ok\n";
        exit(0);
    }

    if ($cmd === 'count-users') {
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$name}", $user, $pass, $opts);
        echo (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        exit(0);
    }

    fwrite(STDERR, "Lệnh không hợp lệ: {$cmd}\n");
    exit(2);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}
