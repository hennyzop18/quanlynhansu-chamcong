# Hàm dùng chung cho setup.ps1 và start.ps1 (Windows 10/11, PowerShell 5.1+)
# Không dùng $ErrorActionPreference = 'Stop' vì PowerShell 5.1 biến output stderr
# của composer/npm thành lỗi. Mỗi bước tự kiểm tra $LASTEXITCODE.
$ErrorActionPreference = 'Continue'
try { [Console]::OutputEncoding = [System.Text.Encoding]::UTF8 } catch {}

$Root  = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$App   = Join-Path $Root 'QLNS'
$Kiosk = Join-Path $Root 'Kiosk-Helper-App'

function Write-Step($msg) { Write-Host ''; Write-Host "==> $msg" -ForegroundColor Cyan }
function Write-Ok($msg)   { Write-Host "    [OK] $msg" -ForegroundColor Green }
function Write-Warn2($msg){ Write-Host "    [!] $msg" -ForegroundColor Yellow }

function Add-ToPath([string]$dir) {
    if ($dir -and (Test-Path $dir) -and (($env:Path -split ';') -notcontains $dir)) {
        $env:Path = "$dir;$env:Path"
    }
}

function Get-PhpVersion([string]$exe) {
    try {
        $v = & $exe -r 'echo PHP_VERSION;' 2>$null
        if ($LASTEXITCODE -ne 0 -or -not $v) { return $null }
        return [version](($v -split '-')[0])
    } catch { return $null }
}

# Tìm PHP >= 8.2: PATH -> XAMPP -> Laragon
function Find-Php {
    $min = [version]'8.2.0'
    $cands = @()
    $cmd = Get-Command php.exe -ErrorAction SilentlyContinue
    if ($cmd) { $cands += $cmd.Source }
    $cands += 'C:\xampp\php\php.exe'
    if (Test-Path 'C:\laragon\bin\php') {
        $cands += Get-ChildItem 'C:\laragon\bin\php' -Directory |
            Sort-Object Name -Descending |
            ForEach-Object { Join-Path $_.FullName 'php.exe' }
    }
    foreach ($c in $cands) {
        if ($c -and (Test-Path $c)) {
            $v = Get-PhpVersion $c
            if ($v -and $v -ge $min) { return $c }
        }
    }
    return $null
}

# Tìm Node.js >= 18 (ưu tiên bản cài trong Program Files\nodejs)
function Find-Node {
    Add-ToPath (Join-Path $env:ProgramFiles 'nodejs')
    $cmd = Get-Command node.exe -ErrorAction SilentlyContinue
    if (-not $cmd) { return $null }
    try {
        $v = ((& $cmd.Source -v) -replace '^v', '').Trim()
        if ([version]$v -ge [version]'18.0.0') { return $cmd.Source }
    } catch {}
    return $null
}

function Test-Port([int]$Port) {
    $c = New-Object System.Net.Sockets.TcpClient
    try {
        $iar = $c.BeginConnect('127.0.0.1', $Port, $null, $null)
        if (-not $iar.AsyncWaitHandle.WaitOne(700)) { return $false }
        $c.EndConnect($iar)
        return $true
    } catch { return $false } finally { $c.Close() }
}

# Đọc 1 giá trị trong QLNS\.env
function Get-DotEnvValue([string]$Key, [string]$Default = '') {
    $f = Join-Path $App '.env'
    if (-not (Test-Path $f)) { return $Default }
    $m = [regex]::Match([IO.File]::ReadAllText($f), "(?m)^[ \t]*$Key[ \t]*=[ \t]*(.*?)[ \t]*(?=\r?$)")
    if (-not $m.Success) { return $Default }
    return $m.Groups[1].Value.Trim('"')
}

# Bật MySQL nếu chưa chạy. Tự bật được MySQL/MariaDB của XAMPP.
function Start-MySqlIfNeeded([int]$Port = 3306) {
    if (Test-Port $Port) { Write-Ok "MySQL đang chạy (cổng $Port)"; return $true }
    $mysqld = 'C:\xampp\mysql\bin\mysqld.exe'
    $ini    = 'C:\xampp\mysql\bin\my.ini'
    if (Test-Path $mysqld) {
        Write-Host '    Đang bật MySQL của XAMPP...'
        Start-Process -FilePath $mysqld `
            -ArgumentList "--defaults-file=`"$ini`"", '--standalone' `
            -WorkingDirectory 'C:\xampp\mysql\bin' -WindowStyle Hidden
        for ($i = 0; $i -lt 30; $i++) {
            Start-Sleep -Seconds 1
            if (Test-Port $Port) { Write-Ok 'MySQL đã bật'; return $true }
        }
    }
    Write-Warn2 'Không bật được MySQL. Mở XAMPP Control Panel (hoặc Laragon), bấm Start MySQL rồi chạy lại.'
    return $false
}
