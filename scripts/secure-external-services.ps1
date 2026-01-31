#Requires -RunAsAdministrator
<#
.SYNOPSIS
    Lock FTP and external MySQL access to prevent external attacks.
.DESCRIPTION
    - Stop FTP service (IIS / FTPSVC)
    - Create firewall rules to block port 21 (FTP) and 3306 (MySQL) from external
    - Localhost connections still allowed (MySQL on this machine remains usable)
.EXAMPLE
    .\secure-external-services.ps1
#>

$ErrorActionPreference = 'Stop'
$RuleNameFtp  = 'Sci-Secure-Block-FTP-21'
$RuleNameMySQL = 'Sci-Secure-Block-MySQL-3306'

Write-Host "=== Lock external access ===" -ForegroundColor Cyan

# 1) Stop FTP service (FTPSVC, Microsoft FTP Service, etc.)
$ftpServiceNames = @('FTPSVC', 'FtpSvc', 'MicrosoftFtpSvc')
$ftpStopped = $false
foreach ($name in $ftpServiceNames) {
    $svc = Get-Service -Name $name -ErrorAction SilentlyContinue
    if ($svc) {
        if ($svc.Status -eq 'Running') {
            Stop-Service -Name $name -Force
            Write-Host "  [OK] Stopped FTP service: $name" -ForegroundColor Green
            $ftpStopped = $true
        } else {
            Write-Host "  [--] FTP service ($name) already stopped" -ForegroundColor Gray
        }
        break
    }
}
if (-not $ftpStopped -and -not (Get-Service -Name $ftpServiceNames[0] -ErrorAction SilentlyContinue)) {
    Write-Host "  [--] FTP service not found on this machine (skipped)" -ForegroundColor Gray
}

# 2) Firewall: Allow localhost first, then block port 21 and 3306 from external
$allowLocalNameFtp  = 'Sci-Secure-Allow-FTP-Localhost'
$allowLocalNameMySQL = 'Sci-Secure-Allow-MySQL-Localhost'
$rules = @(
    @{ Name = $RuleNameFtp;  Port = 21;   Desc = 'FTP';  AllowName = $allowLocalNameFtp }
    @{ Name = $RuleNameMySQL; Port = 3306; Desc = 'MySQL'; AllowName = $allowLocalNameMySQL }
)
foreach ($r in $rules) {
    # Allow 127.0.0.1 only so local use works when locked
    $allowRule = Get-NetFirewallRule -DisplayName $r.AllowName -ErrorAction SilentlyContinue
    if (-not $allowRule) {
        New-NetFirewallRule -DisplayName $r.AllowName `
            -Direction Inbound -LocalPort $r.Port -RemoteAddress 127.0.0.1 `
            -Protocol TCP -Action Allow -Profile Any `
            -Description "Allow localhost $($r.Desc) when locked" | Out-Null
        Write-Host "  [OK] Created firewall rule: allow localhost port $($r.Port)" -ForegroundColor Green
    }
    # Block from external
    $existing = Get-NetFirewallRule -DisplayName $r.Name -ErrorAction SilentlyContinue
    if ($existing) {
        Set-NetFirewallRule -DisplayName $r.Name -Enabled True | Out-Null
        Write-Host "  [OK] Enabled firewall rule (block external): $($r.Name)" -ForegroundColor Green
    } else {
        New-NetFirewallRule -DisplayName $r.Name `
            -Direction Inbound `
            -LocalPort $r.Port `
            -Protocol TCP `
            -Action Block `
            -Profile Any `
            -Description "Block external $($r.Desc) for security (Sci)" | Out-Null
        Write-Host "  [OK] Created firewall rule: block port $($r.Port) from external ($($r.Desc))" -ForegroundColor Green
    }
}

Write-Host "`nDone: External FTP and MySQL access locked." -ForegroundColor Green
Write-Host "To unlock for developers, run: .\enable-external-services.ps1" -ForegroundColor Yellow
