<#
.SYNOPSIS
    เชื่อมต่อ Server 49 ผ่าน FTP (ทดสอบการเชื่อมต่อ / แสดงรายการโฟลเดอร์ / อัปโหลดไฟล์)
.DESCRIPTION
    ใช้ .NET FtpWebRequest หรือ curl เพื่อเชื่อมต่อ FTP ไปยัง 49.231.30.18
    รหัสผ่าน: ใส่เมื่อรัน หรือใช้ตัวแปรสภาพแวดล้อม FTP_PASS
.EXAMPLE
    .\connect_ftp_server_49.ps1
    .\connect_ftp_server_49.ps1 -ListOnly
    .\connect_ftp_server_49.ps1 -UploadFile ".\enable-external-services.ps1" -RemotePath "/sci_root/scripts"
#>

param(
    [string]$FtpHost = "49.231.30.18",
    [int]$FtpPort = 21,
    [string]$FtpUser = "Administrator",
    [string]$FtpPass = "",
    [string]$RemotePath = "/",
    [switch]$ListOnly,
    [string]$UploadFile = "",
    [string]$UploadRemotePath = ""
)

# อ่านจาก env ถ้ามี
if ($env:FTP_HOST) { $FtpHost = $env:FTP_HOST }
if ($env:FTP_PORT) { $FtpPort = [int]$env:FTP_PORT }
if ($env:FTP_USER) { $FtpUser = $env:FTP_USER }
if ($env:FTP_PASS) { $FtpPass = $env:FTP_PASS }
if ($env:FTP_REMOTE_PATH) { $RemotePath = $env:FTP_REMOTE_PATH }

# โหลดจาก ftp_server_49.env ถ้ามี
$envFile = Join-Path $PSScriptRoot "ftp_server_49.env"
if (Test-Path $envFile) {
    Get-Content $envFile | ForEach-Object {
        if ($_ -match '^\s*([^#][^=]+)=(.*)$') {
            $key = $matches[1].Trim()
            $val = $matches[2].Trim()
            switch ($key) {
                'FTP_HOST'   { $FtpHost = $val }
                'FTP_PORT'   { $FtpPort = [int]$val }
                'FTP_USER'   { $FtpUser = $val }
                'FTP_PASS'   { $FtpPass = $val }
                'FTP_REMOTE_PATH' { $RemotePath = $val }
            }
        }
    }
}

if (-not $FtpPass) {
    $sec = Read-Host "รหัสผ่าน FTP สำหรับ $FtpUser@$FtpHost" -AsSecureString
    $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($sec)
    $FtpPass = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
    [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($BSTR)
}

$baseUri = "ftp://${FtpHost}:$FtpPort"

function Get-FtpRequest {
    param([string]$Uri, [string]$Method)
    $req = [System.Net.FtpWebRequest]::Create($Uri)
    $req.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
    $req.Method = $Method
    $req.UseBinary = $true
    $req.UsePassive = $true
    $req.KeepAlive = $false
    return $req
}

Write-Host "=== FTP Server 49 ===" -ForegroundColor Cyan
Write-Host "  Host: $FtpHost`:$FtpPort"
Write-Host "  User: $FtpUser"
Write-Host "  Path: $RemotePath"
Write-Host ""

if ($UploadFile -and (Test-Path $UploadFile)) {
    $fileName = [System.IO.Path]::GetFileName($UploadFile)
    $remoteDir = if ($UploadRemotePath) { $UploadRemotePath.TrimEnd('/') } else { $RemotePath.TrimEnd('/') }
    $fullUri = "$baseUri$remoteDir/$fileName"
    Write-Host "Upload: $UploadFile -> $fullUri"
    $req = Get-FtpRequest -Uri $fullUri -Method [System.Net.WebRequestMethods+Ftp]::UploadFile
    $req.ContentLength = (Get-Item $UploadFile).Length
    $stream = $req.GetRequestStream()
    $fileStream = [System.IO.File]::OpenRead($UploadFile)
    $fileStream.CopyTo($stream)
    $stream.Close()
    $fileStream.Close()
    try {
        $resp = $req.GetResponse()
        Write-Host "  [OK] Upload done." -ForegroundColor Green
        $resp.Close()
    } catch {
        Write-Host "  [FAIL] $($_.Exception.Message)" -ForegroundColor Red
    }
    exit
}

# List directory
$listUri = "$baseUri$RemotePath"
if (-not $listUri.EndsWith("/")) { $listUri += "/" }
Write-Host "List: $listUri"
$req = Get-FtpRequest -Uri $listUri -Method [System.Net.WebRequestMethods+Ftp]::ListDirectory
try {
    $resp = $req.GetResponse()
    $reader = New-Object System.IO.StreamReader($resp.GetResponseStream())
    $lines = $reader.ReadToEnd() -split "`n"
    $reader.Close()
    $resp.Close()
    Write-Host "  [OK] Connected. Contents:" -ForegroundColor Green
    foreach ($line in $lines) {
        $line = $line.Trim()
        if ($line) { Write-Host "    $line" }
    }
} catch {
    Write-Host "  [FAIL] $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "  ตรวจสอบ: 1) Server เปิดพอร์ต 21  2) รัน enable-external-services.ps1 บน server  3) User/Pass ถูกต้อง" -ForegroundColor Yellow
}
