#Requires -RunAsAdministrator
<#
.SYNOPSIS
    Unlock FTP and external MySQL access for developers.
.DESCRIPTION
    - Start FTP service
    - Disable firewall rules that block port 21 and 3306
.EXAMPLE
    .\enable-external-services.ps1
#>

$ErrorActionPreference = 'Stop'
$RuleNameFtp  = 'Sci-Secure-Block-FTP-21'
$RuleNameMySQL = 'Sci-Secure-Block-MySQL-3306'

Write-Host "=== Unlock external access ===" -ForegroundColor Cyan

# 1) Start FTP service
$ftpServiceNames = @('FTPSVC', 'FtpSvc', 'MicrosoftFtpSvc')
$ftpStarted = $false
foreach ($name in $ftpServiceNames) {
    $svc = Get-Service -Name $name -ErrorAction SilentlyContinue
    if ($svc) {
        if ($svc.Status -ne 'Running') {
            Start-Service -Name $name
            Write-Host "  [OK] Started FTP service: $name" -ForegroundColor Green
            $ftpStarted = $true
        } else {
            Write-Host "  [--] FTP service ($name) already running" -ForegroundColor Gray
        }
        break
    }
}
if (-not (Get-Service -Name $ftpServiceNames[0] -ErrorAction SilentlyContinue)) {
    Write-Host "  [--] FTP service not found on this machine (skipped)" -ForegroundColor Gray
}

# 2) Firewall: Disable block rules
foreach ($ruleName in @($RuleNameFtp, $RuleNameMySQL)) {
    $rule = Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue
    if ($rule) {
        Set-NetFirewallRule -DisplayName $ruleName -Enabled False | Out-Null
        Write-Host "  [OK] Disabled firewall rule: $ruleName" -ForegroundColor Green
    } else {
        Write-Host "  [--] Rule not found: $ruleName" -ForegroundColor Gray
    }
}

Write-Host "`nDone: External FTP and MySQL access unlocked (for developers)." -ForegroundColor Green
Write-Host "To lock for security, run: .\secure-external-services.ps1" -ForegroundColor Yellow
