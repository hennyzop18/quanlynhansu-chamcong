# Chạy QLNS trên Windows: bật MySQL, web server Laravel, mở trình duyệt và Kiosk-Helper-App.
# Chạy bằng cách bấm đúp start-windows.bat ở thư mục gốc dự án.

. (Join-Path $PSScriptRoot 'common.ps1')

$Port   = 8000
$AppUrl = "http://127.0.0.1:$Port"

try {
    Write-Host '=============================================' -ForegroundColor Cyan
    Write-Host '   QLNS - KHỞI ĐỘNG'                             -ForegroundColor Cyan
    Write-Host '=============================================' -ForegroundColor Cyan

    $php = Find-Php
    if (-not $php) { throw 'Không tìm thấy PHP 8.2+. Bấm đúp setup-windows.bat trước.' }
    Add-ToPath (Split-Path $php)
    if (-not (Test-Path (Join-Path $App 'vendor\autoload.php')) -or
        -not (Test-Path (Join-Path $App 'public\build\manifest.json')) -or
        -not (Test-Path (Join-Path $App 'node_modules\.qlns-windows'))) {
        throw 'Dự án chưa được cài trên máy này. Bấm đúp setup-windows.bat trước.'
    }

    Write-Step 'MySQL'
    $dbPort = [int](Get-DotEnvValue 'DB_PORT' '3306')
    if (-not (Start-MySqlIfNeeded $dbPort)) { throw 'MySQL chưa chạy.' }

    Write-Step 'Web server Laravel'
    if (Test-Port $Port) {
        Write-Ok "Cổng $Port đã có server chạy, dùng luôn"
    } else {
        # Mở cửa sổ riêng để xem log; đóng cửa sổ đó là tắt server
        Start-Process -FilePath $php -ArgumentList 'artisan', 'serve', '--host=127.0.0.1', "--port=$Port" -WorkingDirectory $App
        $up = $false
        for ($i = 0; $i -lt 40; $i++) {
            Start-Sleep -Seconds 1
            if (Test-Port $Port) { $up = $true; break }
        }
        if (-not $up) { throw "Server không lên ở cổng $Port. Xem cửa sổ php artisan serve để biết lỗi." }
        Write-Ok "Server chạy tại $AppUrl"
    }

    Start-Process "$AppUrl/login"

    Write-Step 'Kiosk-Helper-App'
    $node = Find-Node
    if ($node -and (Test-Path (Join-Path $Kiosk 'node_modules\.qlns-windows'))) {
        Add-ToPath (Split-Path $node)
        Start-Process -FilePath 'cmd.exe' -ArgumentList '/c', 'npm start' -WorkingDirectory $Kiosk -WindowStyle Minimized
        Write-Ok 'Đã mở app Kiosk'
    } else {
        Write-Warn2 'Kiosk-Helper-App chưa được cài, bỏ qua. Chạy setup-windows.bat để cài.'
    }

    Write-Host ''
    Write-Host 'Đang chạy. Tài khoản mẫu: admin@example.com / password' -ForegroundColor Green
    Write-Host 'Muốn tắt: đóng cửa sổ "php artisan serve".'
    exit 0
}
catch {
    Write-Host ''
    Write-Host "[LỖI] $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}
