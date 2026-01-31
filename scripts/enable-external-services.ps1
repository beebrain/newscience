#Requires -RunAsAdministrator
<#
.SYNOPSIS
    เปิด FTP และการเข้าถึง MySQL จากภายนอก สำหรับนักพัฒนา (Unlock)
.DESCRIPTION
    - เริ่มบริการ FTP
    - ลบหรือปิดกฎ Firewall ที่บล็อกพอร์ต 21 และ 3306
.EXAMPLE
    .\enable-external-services.ps1
#>

$ErrorActionPreference = 'Stop'
$RuleNameFtp  = 'Sci-Secure-Block-FTP-21'
$RuleNameMySQL = 'Sci-Secure-Block-MySQL-3306'

Write-Host "=== เปิดการเข้าถึงจากภายนอก (Unlock) ===" -ForegroundColor Cyan

# 1) เริ่มบริการ FTP
$ftpServiceNames = @('FTPSVC', 'FtpSvc', 'MicrosoftFtpSvc')
$ftpStarted = $false
foreach ($name in $ftpServiceNames) {
    $svc = Get-Service -Name $name -ErrorAction SilentlyContinue
    if ($svc) {
        if ($svc.Status -ne 'Running') {
            Start-Service -Name $name
            Write-Host "  [OK] เริ่มบริการ FTP: $name" -ForegroundColor Green
            $ftpStarted = $true
        } else {
            Write-Host "  [--] บริการ FTP ($name) ทำงานอยู่แล้ว" -ForegroundColor Gray
        }
        break
    }
}
if (-not (Get-Service -Name $ftpServiceNames[0] -ErrorAction SilentlyContinue)) {
    Write-Host "  [--] ไม่พบบริการ FTP บนเครื่อง (ข้าม)" -ForegroundColor Gray
}

# 2) Firewall: ปิดกฎบล็อก (หรือลบออก)
foreach ($ruleName in @($RuleNameFtp, $RuleNameMySQL)) {
    $rule = Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
    if ($rule) {
        Set-NetFirewallRule -DisplayName $ruleName -Enabled False | Out-Null
        Write-Host "  [OK] ปิดกฎ Firewall: $ruleName" -ForegroundColor Green
    } else {
        Write-Host "  [--] ไม่พบกฎ: $ruleName" -ForegroundColor Gray
    }
}

Write-Host "`nเสร็จ: เปิดการเข้าถึง FTP และ MySQL จากภายนอกแล้ว (สำหรับนักพัฒนา)" -ForegroundColor Green
Write-Host "ต้องการปิดเพื่อความปลอดภัย ให้รัน: .\secure-external-services.ps1" -ForegroundColor Yellow
