<#
.SYNOPSIS
    Export Evaluation tables from local MySQL and import to production server (49.231.30.18)
.DESCRIPTION
    1. mysqldump evaluate_* and evaluation_referees from local
    2. SCP SQL file to production server
    3. SSH run mysql import on server
    4. Clean up temp file
.EXAMPLE
    .\migrate-evaluate-to-production.ps1
    .\migrate-evaluate-to-production.ps1 -LocalDb "newscience" -DryRun
    .\migrate-evaluate-to-production.ps1 -SkipSsh
#>

param(
    # Local MySQL (XAMPP)
    [string]$LocalHost = "127.0.0.1",
    [int]$LocalPort = 3306,
    [string]$LocalUser = "root",
    [AllowEmptyString()][AllowNull()][string]$LocalPass = $null,
    [string]$LocalDb = "newscience",
    [string]$MysqldumpPath = "C:\xampp\mysql\bin\mysqldump.exe",

    # Production SSH/SCP
    [string]$ServerHost = "49.231.30.18",
    [string]$ServerUser = "Administrator",
    [string]$ServerSqlPath = "C:/inetpub/sci_root/tmp_eval_migrate.sql",

    # Production MySQL (บน server)
    [string]$ServerDbUser = "root",
    [string]$ServerDbPass = "admin@SCI@2026",
    [string]$ServerDb = "newscience",
    [string]$MysqlPathRemote = "C:/xampp/mysql/bin/mysql.exe",

    [switch]$DryRun,
    [switch]$SkipSsh
)

function Write-Step { param($msg) Write-Host "`n==> $msg" -ForegroundColor Cyan }
function Write-OK { param($msg) Write-Host "  [OK]   $msg" -ForegroundColor Green }
function Write-Fail { param($msg) Write-Host "  [FAIL] $msg" -ForegroundColor Red; exit 1 }
function Write-Warn { param($msg) Write-Host "  [WARN] $msg" -ForegroundColor Yellow }

# Evaluation tables to export
$Tables = @(
    "evaluate_teaching",
    "evaluate_scores",
    "evaluate_self",
    "evaluation_referees",
    "evaluate_user_rights"
)

$TmpSql = Join-Path $env:TEMP "eval_migrate_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql"

Write-Host ""
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  Evaluate Data Migration: Local -> Production" -ForegroundColor Cyan
Write-Host "===============================================" -ForegroundColor Cyan
Write-Host "  Local DB  : $LocalDb @ $LocalHost`:$LocalPort"
Write-Host "  Server    : $ServerUser@$ServerHost"
Write-Host "  Server DB : $ServerDb"
Write-Host "  Tables    : $($Tables -join ', ')"
Write-Host "  Temp SQL  : $TmpSql"
if ($DryRun) { Write-Warn "DRY RUN mode - no real commands will execute" }
Write-Host ""

Write-Step "Step 1: Verify mysqldump at $MysqldumpPath"
if (-not (Test-Path $MysqldumpPath)) {
    $found = Get-Command mysqldump -ErrorAction SilentlyContinue
    if ($found) {
        $MysqldumpPath = $found.Source
        Write-OK "Found mysqldump in PATH: $MysqldumpPath"
    }
    else {
        Write-Fail "mysqldump not found at '$MysqldumpPath' and not in PATH. Use -MysqldumpPath."
    }
}
else {
    Write-OK "Found mysqldump: $MysqldumpPath"
}

Write-Step "Step 2: Local MySQL credentials"
if ($null -eq $LocalPass) {
    $sec = Read-Host "  Local MySQL password for user=$LocalUser db=$LocalDb (press Enter if none)" -AsSecureString
    $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($sec)
    $LocalPass = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
    [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($BSTR)
}

Write-Step "Step 3: Check which tables exist in local DB"
$mysqlExe = $MysqldumpPath -replace "mysqldump", "mysql"

$existTables = @()
foreach ($tbl in $Tables) {
    $checkArgs = @("-h", $LocalHost, "-P", $LocalPort, "-u", $LocalUser)
    if ($LocalPass) { $checkArgs += "-p$LocalPass" }
    $checkSQL = "SELECT 1 FROM information_schema.tables WHERE table_schema='$LocalDb' AND table_name='$tbl' LIMIT 1;"
    $checkArgs += @("-e", $checkSQL, "--skip-column-names", $LocalDb)

    if (-not $DryRun) {
        $result = & $mysqlExe @checkArgs 2>&1
        if ($result -match "1") {
            $existTables += $tbl
            Write-OK "  $tbl  [found]"
        }
        else {
            Write-Warn "  $tbl  [not found - skipping]"
        }
    }
    else {
        $existTables += $tbl
        Write-OK "  $tbl  [DRY RUN - assumed present]"
    }
}

if ($existTables.Count -eq 0) {
    Write-Fail "No tables found in '$LocalDb'. Check -LocalDb and -LocalUser/-LocalPass."
}

Write-Step "Step 4: Export tables ($($existTables -join ', ')) from local"
$dumpArgs = @(
    "-h", $LocalHost,
    "-P", $LocalPort,
    "-u", $LocalUser
)
if ($LocalPass) { $dumpArgs += "-p$LocalPass" }
$dumpArgs += @(
    "--single-transaction",
    "--routines",
    "--triggers",
    "--add-drop-table",
    "--complete-insert",
    "--extended-insert",
    "--set-charset",
    "--default-character-set=utf8mb4",
    $LocalDb
)
$dumpArgs += $existTables

if ($DryRun) {
    Write-OK "DRY RUN: mysqldump [args] > $TmpSql"
}
else {
    & $MysqldumpPath @dumpArgs | Out-File -FilePath $TmpSql -Encoding utf8
    if ($LASTEXITCODE -ne 0 -or -not (Test-Path $TmpSql)) {
        Write-Fail "mysqldump failed (exit=$LASTEXITCODE)"
    }
    $sizeMB = [Math]::Round((Get-Item $TmpSql).Length / 1MB, 2)
    Write-OK "Exported: $TmpSql  ($sizeMB MB, $($existTables.Count) tables)"
}

if (-not $SkipSsh) {
    Write-Step "Step 5: Verify SCP/SSH availability"
    $scpCmd = Get-Command scp -ErrorAction SilentlyContinue
    $sshCmd = Get-Command ssh -ErrorAction SilentlyContinue
    if (-not $scpCmd) { Write-Fail "scp not found. Install OpenSSH Client: Settings > Apps > Optional Features" }
    if (-not $sshCmd) { Write-Fail "ssh not found. Install OpenSSH Client." }
    Write-OK "scp: $($scpCmd.Source)"
    Write-OK "ssh: $($sshCmd.Source)"
}

if (-not $SkipSsh) {
    Write-Step "Step 6: SCP upload SQL -> $ServerUser@$ServerHost`:$ServerSqlPath"
    $remoteTarget = "${ServerUser}@${ServerHost}:$($ServerSqlPath -replace '\\','/')"
    if ($DryRun) {
        Write-OK "DRY RUN: scp $TmpSql $remoteTarget"
    }
    else {
        & scp $TmpSql $remoteTarget
        if ($LASTEXITCODE -ne 0) { Write-Fail "SCP upload failed" }
        Write-OK "Upload OK"
    }
}

if (-not $SkipSsh) {
    Write-Step "Step 7: SSH run mysql import on server"
    $serverSqlWin = $ServerSqlPath -replace '/', '\'
    # Simple cmd string — no embedded quote escaping needed (paths have no spaces)
    $remoteCmd = "cmd /c $MysqlPathRemote -u $ServerDbUser -p$ServerDbPass $ServerDb < $serverSqlWin"

    Write-Host "  Remote: $remoteCmd" -ForegroundColor Gray

    if ($DryRun) {
        Write-OK "DRY RUN: ssh ${ServerUser}@${ServerHost} [import cmd]"
    }
    else {
        $sshResult = & ssh "${ServerUser}@${ServerHost}" $remoteCmd 2>&1
        Write-Host "  SSH output: $sshResult" -ForegroundColor Gray
        if ($LASTEXITCODE -ne 0) {
            Write-Warn "Import may have issues (exit=$LASTEXITCODE). Check SSH output above."
        }
        else {
            Write-OK "Import OK"
        }
    }
}

Write-Step "Step 8: Cleanup temp SQL file"
if (-not $DryRun -and (Test-Path $TmpSql)) {
    Remove-Item $TmpSql -Force
    Write-OK "Deleted $TmpSql"
}
else {
    Write-OK "Skipped (DryRun or file not found)"
}

Write-Host ""
Write-Host "===============================================" -ForegroundColor Green
Write-Host "  Migration complete!" -ForegroundColor Green
Write-Host "===============================================" -ForegroundColor Green
Write-Host "  Tables   : $($existTables -join ', ')"
Write-Host "  Server   : $ServerHost"
Write-Host "  Database : $ServerDb"
if ($SkipSsh) {
    Write-Warn "  SkipSsh=true: SQL was exported to $TmpSql only (not imported)"
    Write-Warn "  Import manually via phpMyAdmin or mysql CLI"
}
Write-Host ""
