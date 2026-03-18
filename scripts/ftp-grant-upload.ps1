<#
.SYNOPSIS
    Upload _grant_remote.php to production server via FTP
#>
param(
    [string]$FtpHost    = "49.231.30.18",
    [int]$FtpPort       = 21,
    [string]$FtpUser    = "Administrator",
    [AllowEmptyString()][AllowNull()][string]$FtpPass = $null,
    [string]$RemotePath = "/sci_root/public"
)

if ($null -eq $FtpPass) {
    $sec = Read-Host "FTP password for $FtpUser@$FtpHost" -AsSecureString
    $BSTR = [System.Runtime.InteropServices.Marshal]::SecureStringToBSTR($sec)
    $FtpPass = [System.Runtime.InteropServices.Marshal]::PtrToStringAuto($BSTR)
    [System.Runtime.InteropServices.Marshal]::ZeroFreeBSTR($BSTR)
}

$localFile = "$PSScriptRoot\_grant_remote.php"
$remoteFile = "$RemotePath/_grant_remote.php"
$uri = "ftp://${FtpHost}:${FtpPort}${remoteFile}"

Write-Host "Uploading: $localFile -> $uri" -ForegroundColor Cyan

$creds = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
try {
    $req = [System.Net.FtpWebRequest]::Create($uri)
    $req.Credentials = $creds
    $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $req.UseBinary = $true
    $req.UsePassive = $true
    
    $bytes = [System.IO.File]::ReadAllBytes($localFile)
    $req.ContentLength = $bytes.Length
    $stream = $req.GetRequestStream()
    $stream.Write($bytes, 0, $bytes.Length)
    $stream.Close()
    
    $resp = $req.GetResponse()
    $resp.Close()
    
    Write-Host "[OK] Uploaded successfully" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next step: Call the grant script via browser or curl:" -ForegroundColor Yellow
    Write-Host "  URL: https://sci.uru.ac.th/_grant_remote.php?t=grant2026" -ForegroundColor Cyan
} catch {
    Write-Host "[FAIL] $($_.Exception.Message)" -ForegroundColor Red
}
