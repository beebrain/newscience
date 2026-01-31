#Requires -RunAsAdministrator
<#
.SYNOPSIS
    ปิด FTP และการเข้าถึง MySQL จากภายนอก เพื่อป้องกันการโจมตี (Lock)
.DESCRIPTION
    - หยุดบริการ FTP (IIS / FTPSVC)
    - สร้างกฎ Firewall บล็อกพอร์ต 21 (FTP) และ 3306 (MySQL) จากภายนอก
    - การเชื่อมต่อ localhost ยังใช้ได้ (MySQL ภายในเครื่องยังใช้ได้)
.EXAMPLE
    .\secure-external-services.ps1
#>

$ErrorActionPreference = 'Stop'
$RuleNameFtp  = 'Sci-Secure-Block-FTP-21'
$RuleNameMySQL = 'Sci-Secure-Block-MySQL-3306'

Write-Host "=== ปิดการเข้าถึงจากภายนอก (Lock) ===" -ForegroundColor Cyan

# 1) หยุดบริการ FTP (ชื่อบริการอาจเป็น FTPSVC, Microsoft FTP Service ฯลฯ)
$ftpServiceNames = @('FTPSVC', 'FtpSvc', 'MicrosoftFtpSvc')
$ftpStopped = $false
foreach ($name in $ftpServiceNames) {
    $svc = Get-Service -Name $name -ErrorAction SilentlyContinue
    if ($svc) {
        if ($svc.Status -eq 'Running') {
            Stop-Service -Name $name -Force
            Write-Host "  [OK] หยุดบริการ FTP: $name" -ForegroundColor Green
            $ftpStopped = $true
        } else {
            Write-Host "  [--] บริการ FTP ($name) หยุดอยู่แล้ว" -ForegroundColor Gray
        }
        break
    }
}
if (-not $ftpStopped -and -not (Get-Service -Name $ftpServiceNames[0] -ErrorAction SilentlyContinue)) {
    Write-Host "  [--] ไม่พบบริการ FTP บนเครื่อง (ข้าม)" -ForegroundColor Gray
}

# 2) Firewall: อนุญาต localhost ก่อน แล้วบล็อกพอร์ต 21 และ 3306 จากภายนอก
$allowLocalNameFtp  = 'Sci-Secure-Allow-FTP-Localhost'
$allowLocalNameMySQL = 'Sci-Secure-Allow-MySQL-Localhost'
$rules = @(
    @{ Name = $RuleNameFtp;  Port = 21;   Desc = 'FTP';  AllowName = $allowLocalNameFtp }
    @{ Name = $RuleNameMySQL; Port = 3306; Desc = 'MySQL'; AllowName = $allowLocalNameMySQL }
)
foreach ($r in $rules) {
    # อนุญาตเฉพาะ 127.0.0.1 (localhost) เพื่อให้ใช้ในเครื่องได้เมื่อล็อก
    $allowRule = Get-NetFirewallRule -DisplayName $r.AllowName -ErrorAction SilentlyContinue
    if (-not $allowRule) {
        New-NetFirewallRule -DisplayName $r.AllowName `
            -Direction Inbound -LocalPort $r.Port -RemoteAddress 127.0.0.1 `
            -Protocol TCP -Action Allow -Profile Any `
            -Description "Allow localhost $($r.Desc) when locked" | Out-Null
        Write-Host "  [OK] สร้างกฎอนุญาต localhost พอร์ต $($r.Port)" -ForegroundColor Green
    }
    # บล็อกจากที่อื่น (ภายนอก)
    $existing = Get-NetFirewallRule -DisplayName $r.Name -ErrorAction SilentlyContinue
    if ($existing) {
        Set-NetFirewallRule -DisplayName $r.Name -Enabled True | Out-Null
        Write-Host "  [OK] เปิดใช้กฎ Firewall บล็อกภายนอก: $($r.Name)" -ForegroundColor Green
    } else {
        New-NetFirewallRule -DisplayName $r.Name `
            -Direction Inbound `
            -LocalPort $r.Port `
            -Protocol TCP `
            -Action Block `
            -Profile Any `
            -Description "Block external $($r.Desc) for security (Sci)" | Out-Null
        Write-Host "  [OK] สร้างกฎ Firewall บล็อกพอร์ต $($r.Port) จากภายนอก ($($r.Desc))" -ForegroundColor Green
    }
}

Write-Host "`nเสร็จ: ปิดการเข้าถึง FTP และ MySQL จากภายนอกแล้ว" -ForegroundColor Green
Write-Host "ต้องการเปิดสำหรับนักพัฒนา ให้รัน: .\enable-external-services.ps1" -ForegroundColor Yellow
