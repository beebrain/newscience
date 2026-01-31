<#
.SYNOPSIS
    อัปโหลด 2 ไฟล์ PowerShell (secure/enable-external-services.ps1) เข้า server ผ่าน SCP
.DESCRIPTION
    ใช้ SCP (OpenSSH) copy ไฟล์ไปยัง server
    ต้องมี: SSH user, host, path บน server (แก้ในตัวแปรด้านล่าง หรือส่งเป็น parameter)
.EXAMPLE
    .\upload-secure-scripts-to-server.ps1
    .\upload-secure-scripts-to-server.ps1 -ServerUser Administrator -RemotePath "C:\inetpub\sci_root\scripts"
#>

param(
    [string]$ServerHost = "49.231.30.18",
    [string]$ServerUser = "Administrator",
    [string]$RemotePath = "C:\inetpub\sci_root\scripts"
)

$ScriptDir = $PSScriptRoot
$Files = @(
    "enable-external-services.ps1",
    "secure-external-services.ps1"
)

Write-Host "=== อัปโหลดไฟล์เข้า Server ===" -ForegroundColor Cyan
Write-Host "  Host: $ServerUser@$ServerHost"
Write-Host "  Remote path: $RemotePath"
Write-Host ""

# ใช้ scp (Windows 10+ มี OpenSSH client)
$scp = Get-Command scp -ErrorAction SilentlyContinue
if (-not $scp) {
    Write-Host "[ERROR] ไม่พบคำสั่ง scp (ติดตั้ง OpenSSH Client ใน Windows)" -ForegroundColor Red
    Write-Host "  Settings > Apps > Optional features > Add OpenSSH Client" -ForegroundColor Yellow
    exit 1
}

foreach ($f in $Files) {
    $local = Join-Path $ScriptDir $f
    if (-not (Test-Path $local)) {
        Write-Host "  [SKIP] ไม่พบไฟล์: $f" -ForegroundColor Yellow
        continue
    }
    # SCP ไปยัง Windows server: path แบบ C:\... หรือ /c/... ขึ้นกับ OpenSSH server
    $remote = "${ServerUser}@${ServerHost}:$($RemotePath -replace '\\','/')/$f"
    Write-Host "  Upload: $f -> $remote"
    & scp $local $remote
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  [OK] $f" -ForegroundColor Green
    } else {
        Write-Host "  [FAIL] $f" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "เสร็จ" -ForegroundColor Green
Write-Host "ถ้า RemotePath ผิด (เช่น Linux ใช้ /home/xxx/scripts) ให้ส่ง parameter:" -ForegroundColor Gray
Write-Host '  .\upload-secure-scripts-to-server.ps1 -ServerUser Administrator -RemotePath "/path/on/server/scripts"' -ForegroundColor Gray
