# newScience Backup - Windows Task Scheduler Setup
# Registers a daily task at 02:00 to run backup.py
# Usage: .\setup_scheduler_windows.ps1 [--remove]
# Run as Administrator to create/remove the scheduled task.

param(
    [switch]$Remove
)

$ErrorActionPreference = "Stop"
$ToolDir = $PSScriptRoot
$PythonExe = (Get-Command python -ErrorAction SilentlyContinue).Source
if (-not $PythonExe) {
    $PythonExe = (Get-Command py -ErrorAction SilentlyContinue).Source
}
if (-not $PythonExe) {
    Write-Host "Error: python not found in PATH. Install Python or add it to PATH." -ForegroundColor Red
    exit 1
}

$BackupScript = Join-Path $ToolDir "backup.py"
$ConfigFile = Join-Path $ToolDir "backup_config.json"
$LogDir = Join-Path $ToolDir "backups"
$LogFile = Join-Path $LogDir "scheduled_backup.log"

if (-not (Test-Path $BackupScript)) {
    Write-Host "Error: backup.py not found at $BackupScript" -ForegroundColor Red
    exit 1
}

$TaskName = "newScience-Backup"
$TaskDescription = "Daily backup of newScience database and files (newscience-backup)"
$Action = New-ScheduledTaskAction -Execute $PythonExe -Argument "`"$BackupScript`"" -WorkingDirectory $ToolDir
$Trigger = New-ScheduledTaskTrigger -Daily -At "02:00"
$Settings = New-ScheduledTaskSettingsSet -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -StartWhenAvailable

if ($Remove) {
    try {
        Unregister-ScheduledTask -TaskName $TaskName -Confirm:$false -ErrorAction Stop
        Write-Host "Scheduled task '$TaskName' removed." -ForegroundColor Green
    } catch {
        if ($_.Exception.Message -match "cannot find") {
            Write-Host "Task '$TaskName' does not exist." -ForegroundColor Yellow
        } else {
            Write-Host "Error removing task: $_" -ForegroundColor Red
            exit 1
        }
    }
    exit 0
}

# Ensure log directory exists
if (-not (Test-Path $LogDir)) {
    New-Item -ItemType Directory -Path $LogDir -Force | Out-Null
}

# Run Python backup and append output to log file
$LogAction = New-ScheduledTaskAction `
    -Execute "cmd.exe" `
    -Argument "/c cd /d `"$ToolDir`" && `"$PythonExe`" `"$BackupScript`" >> `"$LogFile`" 2>&1" `
    -WorkingDirectory $ToolDir

try {
    Register-ScheduledTask -TaskName $TaskName -Action $LogAction -Trigger $Trigger -Settings $Settings -Description $TaskDescription -Force | Out-Null
    Write-Host "Scheduled task '$TaskName' created. Runs daily at 02:00." -ForegroundColor Green
    Write-Host "Log file: $LogFile" -ForegroundColor Cyan
    Write-Host "To remove: .\setup_scheduler_windows.ps1 -Remove" -ForegroundColor Gray
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
    Write-Host "Try running PowerShell as Administrator." -ForegroundColor Yellow
    exit 1
}
