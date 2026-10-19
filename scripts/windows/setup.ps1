# Cài đặt QLNS + Kiosk-Helper-App trên Windows 10/11.
# Chạy bằng cách bấm đúp setup-windows.bat ở thư mục gốc dự án.
# Chạy lại nhiều lần vẫn an toàn: bước nào đã xong sẽ được bỏ qua.

. (Join-Path $PSScriptRoot 'common.ps1')

# ===== CẤU HÌNH DATABASE =====
# XAMPP/Laragon mặc định: user root, không có mật khẩu.
# Nếu MySQL của bạn có mật khẩu, sửa $DbPass ở đây.
$DbHost = '127.0.0.1'
$DbPort = 3306
$DbName = 'qlns'
$DbUser = 'root'
$DbPass = ''
# =============================

$AppUrl = 'http://127.0.0.1:8000'
$script:ComposerPhar = $null

function Assert-Exit([string]$What) {
    if ($LASTEXITCODE -ne 0) { throw "$What thất bại (mã lỗi $LASTEXITCODE)." }
}

function Install-ByWinget([string]$Id, [string]$Label) {
    if (-not (Get-Command winget.exe -ErrorAction SilentlyContinue)) {
        throw "Máy không có winget. Hãy tự cài $Label rồi chạy lại setup-windows.bat."
    }
    Write-Host "    Đang cài $Label qua winget (có thể hiện hộp thoại xin quyền Admin, bấm Yes)..."
    & winget install -e --id $Id --silent --accept-package-agreements --accept-source-agreements
    # winget trả mã khác 0 cả khi đã cài sẵn, nên không dừng ở đây; bước sau sẽ kiểm tra lại.
}

function Invoke-Composer {
    if ($script:ComposerPhar) { & $script:Php $script:ComposerPhar @args }
    else { & composer @args }
}

function Reset-NodeModules([string]$Dir) {
    $nm = Join-Path $Dir 'node_modules'
    $mark = Join-Path $nm '.qlns-windows'
    if ((Test-Path $nm) -and -not (Test-Path $mark)) {
        Write-Host "    Xoá node_modules cũ (bản macOS) trong $(Split-Path $Dir -Leaf)..."
        cmd /c rmdir /s /q "$nm"
        if (Test-Path $nm) { throw "Không xoá được $nm. Đóng VS Code/terminal đang mở thư mục này rồi chạy lại." }
    }
}

function Set-NodeModulesMark([string]$Dir) {
    $nm = Join-Path $Dir 'node_modules'
    if (Test-Path $nm) { [IO.File]::WriteAllText((Join-Path $nm '.qlns-windows'), 'installed on windows') }
}

$EnvTemplate = @"
APP_NAME="QLNS"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE="Asia/Ho_Chi_Minh"
APP_URL=$AppUrl

APP_LOCALE=vi
APP_FALLBACK_LOCALE=vi

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=$DbHost
DB_PORT=$DbPort
DB_DATABASE=$DbName
DB_USERNAME=$DbUser
DB_PASSWORD=$DbPass

SESSION_DRIVER=database
SESSION_LIFETIME=120
QUEUE_CONNECTION=sync
CACHE_STORE=database
MAIL_MAILER=log
"@

try {
    Write-Host '=============================================' -ForegroundColor Cyan
    Write-Host '   QLNS - CÀI ĐẶT CHO WINDOWS'                  -ForegroundColor Cyan
    Write-Host '============================================='  -ForegroundColor Cyan
    Write-Host "Thư mục dự án: $Root"

    if (-not (Test-Path (Join-Path $App 'artisan'))) { throw "Không thấy $App\artisan. Đặt file .bat ở thư mục gốc dự án." }

    # ---------------------------------------------------------------
    Write-Step '1/9 Kiểm tra PHP >= 8.2'
    $script:Php = Find-Php
    if (-not $script:Php) {
        Install-ByWinget 'ApacheFriends.Xampp.8.2' 'XAMPP 8.2 (PHP + MySQL)'
        $script:Php = Find-Php
    }
    if (-not $script:Php) { throw 'Không tìm thấy PHP 8.2+. Cài XAMPP 8.2 (https://www.apachefriends.org) vào C:\xampp rồi chạy lại.' }
    $php = $script:Php
    $phpDir = Split-Path $php
    Add-ToPath $phpDir
    Write-Ok "PHP $(Get-PhpVersion $php) tại $php"

    # ---------------------------------------------------------------
    Write-Step '2/9 Bật các extension PHP cần thiết'
    $ini = (& $php -r 'echo php_ini_loaded_file();')
    if (-not $ini) {
        $ini = Join-Path $phpDir 'php.ini'
        $tpl = Join-Path $phpDir 'php.ini-development'
        if (-not (Test-Path $tpl)) { throw "Không có php.ini trong $phpDir." }
        Copy-Item $tpl $ini
        $c = [IO.File]::ReadAllText($ini)
        $extDir = 'extension_dir = "' + (Join-Path $phpDir 'ext') + '"'
        $c = [regex]::Replace($c, '(?m)^[ \t]*;[ \t]*extension_dir[ \t]*=[ \t]*"ext"[ \t]*(?=\r?$)',
            [Text.RegularExpressions.MatchEvaluator] { param($m) $extDir })
        [IO.File]::WriteAllText($ini, $c)
        Write-Ok "Đã tạo $ini"
    }
    $needExt = @('curl', 'fileinfo', 'gd', 'mbstring', 'openssl', 'pdo_mysql', 'zip')
    $loaded = @(& $php -m | ForEach-Object { $_.Trim().ToLower() })
    $missing = @($needExt | Where-Object { $loaded -notcontains $_ })
    if ($missing.Count -gt 0) {
        if (-not (Test-Path "$ini.qlns-backup")) { Copy-Item $ini "$ini.qlns-backup" }
        $c = [IO.File]::ReadAllText($ini)
        foreach ($e in $missing) {
            $pattern = '(?m)^[ \t]*;[ \t]*extension[ \t]*=[ \t]*(php_)?' + $e + '(\.dll)?[ \t]*(?=\r?$)'
            $newLine = "extension=$e"
            if ([regex]::IsMatch($c, $pattern)) {
                $c = [regex]::Replace($c, $pattern, [Text.RegularExpressions.MatchEvaluator] { param($m) $newLine })
            } else {
                $c = $c.TrimEnd() + "`r`n$newLine`r`n"
            }
        }
        [IO.File]::WriteAllText($ini, $c)
        $loaded = @(& $php -m | ForEach-Object { $_.Trim().ToLower() })
        $missing = @($needExt | Where-Object { $loaded -notcontains $_ })
        if ($missing.Count -gt 0) { throw "Vẫn thiếu extension: $($missing -join ', '). Mở $ini và bật thủ công." }
    }
    Write-Ok "Đủ extension: $($needExt -join ', ')"

    # ---------------------------------------------------------------
    Write-Step '3/9 Kiểm tra Composer'
    if (Get-Command composer -ErrorAction SilentlyContinue) {
        Write-Ok 'Dùng Composer đã cài trên máy'
    } else {
        $toolsDir = Join-Path $Root '.tools'
        $script:ComposerPhar = Join-Path $toolsDir 'composer.phar'
        if (-not (Test-Path $script:ComposerPhar)) {
            New-Item -ItemType Directory -Force $toolsDir | Out-Null
            Write-Host '    Đang tải composer.phar...'
            [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
            Invoke-WebRequest -UseBasicParsing 'https://getcomposer.org/download/latest-stable/composer.phar' -OutFile $script:ComposerPhar -ErrorAction Stop
        }
        Write-Ok "Dùng $($script:ComposerPhar)"
    }

    # ---------------------------------------------------------------
    Write-Step '4/9 Kiểm tra Node.js >= 18'
    $node = Find-Node
    if (-not $node) {
        Install-ByWinget 'OpenJS.NodeJS.LTS' 'Node.js LTS'
        $node = Find-Node
    }
    if (-not $node) { throw 'Không tìm thấy Node.js 18+. Cài Node LTS từ https://nodejs.org rồi chạy lại.' }
    Add-ToPath (Split-Path $node)
    Write-Ok "Node $(& $node -v)"

    # ---------------------------------------------------------------
    Write-Step '5/9 MySQL và database'
    if (-not (Start-MySqlIfNeeded $DbPort)) { throw 'MySQL chưa chạy.' }
    $env:QLNS_DB_HOST = $DbHost
    $env:QLNS_DB_PORT = "$DbPort"
    $env:QLNS_DB_NAME = $DbName
    $env:QLNS_DB_USER = $DbUser
    $env:QLNS_DB_PASS = $DbPass
    $dbTool = Join-Path $PSScriptRoot 'db-tool.php'
    & $php $dbTool create | Out-Null
    if ($LASTEXITCODE -ne 0) { throw "Không tạo được database '$DbName'. Kiểm tra user/mật khẩu MySQL ở đầu file scripts\windows\setup.ps1." }
    Write-Ok "Database '$DbName' sẵn sàng"

    # ---------------------------------------------------------------
    Write-Step '6/9 Cấu hình QLNS\.env'
    $envFile = Join-Path $App '.env'
    if (Test-Path $envFile) {
        $bak = Join-Path $App '.env.mac.bak'
        if (-not (Test-Path $bak)) { Copy-Item $envFile $bak; Write-Ok 'Đã sao lưu .env cũ thành .env.mac.bak' }
    } else {
        [IO.File]::WriteAllText($envFile, $EnvTemplate)
        Write-Ok 'Đã tạo .env mới'
    }
    $c = [IO.File]::ReadAllText($envFile)
    $values = [ordered]@{
        APP_URL       = $AppUrl
        DB_CONNECTION = 'mysql'
        DB_HOST       = $DbHost
        DB_PORT       = "$DbPort"
        DB_DATABASE   = $DbName
        DB_USERNAME   = $DbUser
        DB_PASSWORD   = $DbPass
    }
    foreach ($k in $values.Keys) {
        $line = "$k=$($values[$k])"
        $pat = "(?m)^[ \t]*$k[ \t]*=.*?(?=\r?$)"
        if ([regex]::IsMatch($c, $pat)) {
            $c = [regex]::Replace($c, $pat, [Text.RegularExpressions.MatchEvaluator] { param($m) $line })
        } else {
            $c = $c.TrimEnd() + "`r`n$line`r`n"
        }
    }
    [IO.File]::WriteAllText($envFile, $c)
    Write-Ok "DB = $DbUser@${DbHost}:$DbPort/$DbName, APP_URL = $AppUrl"

    # ---------------------------------------------------------------
    Write-Step '7/9 Dọn các file mang sang từ macOS'
    # public\storage trên Mac là symlink tuyệt đối /Users/..., sang Windows bị hỏng
    $pubStorage = Join-Path $App 'public\storage'
    $item = Get-Item -LiteralPath $pubStorage -Force -ErrorAction SilentlyContinue
    if ($item) {
        if ($item.Attributes -band [IO.FileAttributes]::ReparsePoint) {
            cmd /c rmdir "$pubStorage"
        } elseif (-not $item.PSIsContainer) {
            Remove-Item -LiteralPath $pubStorage -Force
        } else {
            $dest = "storage.mac-copy-$(Get-Date -Format yyyyMMddHHmmss)"
            Rename-Item -LiteralPath $pubStorage $dest
            Write-Warn2 "public\storage là thư mục thật, đã đổi tên thành public\$dest"
        }
    }
    Reset-NodeModules $App
    Reset-NodeModules $Kiosk
    Write-Ok 'Xong'

    # Model AI của face-api.js bị .gitignore loại ra, clone từ git sẽ không có -> tự tải
    $modelDir = Join-Path $App 'public\models'
    $modelBase = 'https://github.com/justadudewhohacks/face-api.js/raw/master/weights'
    $modelFiles = @(
        'ssd_mobilenetv1_model-weights_manifest.json', 'ssd_mobilenetv1_model-shard1', 'ssd_mobilenetv1_model-shard2',
        'face_landmark_68_model-weights_manifest.json', 'face_landmark_68_model-shard1',
        'face_recognition_model-weights_manifest.json', 'face_recognition_model-shard1', 'face_recognition_model-shard2',
        'tiny_face_detector_model-weights_manifest.json', 'tiny_face_detector_model-shard1'
    )
    New-Item -ItemType Directory -Force $modelDir | Out-Null
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    foreach ($mf in $modelFiles) {
        $dest = Join-Path $modelDir $mf
        if (-not (Test-Path $dest) -or (Get-Item $dest).Length -eq 0) {
            Write-Host "    Đang tải model $mf..."
            Invoke-WebRequest -UseBasicParsing "$modelBase/$mf" -OutFile $dest -ErrorAction Stop
        }
    }
    Write-Ok 'Đủ model nhận diện khuôn mặt'

    # ---------------------------------------------------------------
    Write-Step '8/9 Cài thư viện QLNS và khởi tạo database'
    Set-Location $App
    # Laravel cần các thư mục này, thiếu là composer install lỗi ở bước package:discover
    foreach ($d in 'bootstrap\cache', 'storage\framework\cache\data', 'storage\framework\sessions', 'storage\framework\views', 'storage\logs') {
        New-Item -ItemType Directory -Force (Join-Path $App $d) | Out-Null
    }
    Invoke-Composer install --no-interaction --prefer-dist
    Assert-Exit 'composer install'

    if ([regex]::IsMatch([IO.File]::ReadAllText($envFile), '(?m)^APP_KEY[ \t]*=[ \t]*(?=\r?$)')) {
        & $php artisan key:generate --force
        Assert-Exit 'php artisan key:generate'
    }

    & $php artisan migrate --force
    Assert-Exit 'php artisan migrate'

    $users = & $php $dbTool count-users
    if ($LASTEXITCODE -eq 0 -and [int]$users -eq 0) {
        Write-Host '    Database trống, đang nạp dữ liệu mẫu (seed)...'
        & $php artisan db:seed --force
        Assert-Exit 'php artisan db:seed'
    } else {
        Write-Ok "Database đã có $users user, bỏ qua seed"
    }

    if (-not (Test-Path $pubStorage)) {
        & $php artisan storage:link
        Assert-Exit 'php artisan storage:link'
    }

    & npm install
    Assert-Exit 'npm install (QLNS)'
    Set-NodeModulesMark $App

    & npm run build
    if ($LASTEXITCODE -ne 0) {
        # Lỗi hay gặp: package-lock tạo trên Mac thiếu binary win32 của rollup/esbuild
        Write-Warn2 'Build lỗi, thử cài lại node_modules không dùng package-lock của Mac...'
        $lock = Join-Path $App 'package-lock.json'
        if (Test-Path $lock) { Move-Item -Force $lock (Join-Path $App 'package-lock.mac.json') }
        cmd /c rmdir /s /q "$(Join-Path $App 'node_modules')"
        & npm install
        Assert-Exit 'npm install (QLNS, lần 2)'
        Set-NodeModulesMark $App
        & npm run build
        Assert-Exit 'npm run build'
    }

    & $php artisan optimize:clear
    Assert-Exit 'php artisan optimize:clear'

    # ---------------------------------------------------------------
    Write-Step '9/9 Cài Kiosk-Helper-App (Electron)'
    Set-Location $Kiosk
    & npm install
    Assert-Exit 'npm install (Kiosk-Helper-App)'
    Set-NodeModulesMark $Kiosk

    Set-Location $Root
    Write-Host ''
    Write-Host '=============================================' -ForegroundColor Green
    Write-Host '   CÀI ĐẶT XONG'                                -ForegroundColor Green
    Write-Host '=============================================' -ForegroundColor Green
    Write-Host 'Bấm đúp start-windows.bat để chạy.'
    Write-Host "Web: $AppUrl   (tài khoản mẫu: admin@example.com / password)"
    exit 0
}
catch {
    Set-Location $Root
    Write-Host ''
    Write-Host "[LỖI] $($_.Exception.Message)" -ForegroundColor Red
    Write-Host 'Sửa lỗi trên rồi bấm đúp setup-windows.bat lần nữa (các bước đã xong sẽ được bỏ qua).'
    exit 1
}
