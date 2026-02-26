# Fix double-encoded UTF-8 Thai text in Edoc view files
# The files were saved through cp874 (Windows Thai) encoding, causing mojibake

$files = @(
    'app\Views\edoc\documents\admin_documents.php',
    'app\Views\edoc\documents\showEdoc.php',
    'app\Views\edoc\documents\document_view.php'
)

$utf8 = [System.Text.Encoding]::UTF8
$cp874 = [System.Text.Encoding]::GetEncoding(874)

foreach ($file in $files) {
    $fullPath = Join-Path 'c:\xampp\htdocs\newScience' $file
    if (-not (Test-Path $fullPath)) {
        Write-Host "SKIP: $file not found" -ForegroundColor Yellow
        continue
    }

    # Backup
    $backupPath = "$fullPath.bak-encoding"
    if (-not (Test-Path $backupPath)) {
        Copy-Item $fullPath $backupPath
        Write-Host "Backup: $backupPath" -ForegroundColor Cyan
    }

    # Read raw bytes
    $bytes = [System.IO.File]::ReadAllBytes($fullPath)
    
    # Decode as UTF-8 (current garbled state)
    $garbled = $utf8.GetString($bytes)
    
    # Encode back as cp874 (reverses the double-encoding)
    $originalBytes = $cp874.GetBytes($garbled)
    
    # Write the corrected bytes
    [System.IO.File]::WriteAllBytes($fullPath, $originalBytes)
    
    # Verify
    $fixed = $utf8.GetString([System.IO.File]::ReadAllBytes($fullPath))
    
    if ($fixed -match '[\u0E01-\u0E5B]{2,}') {
        Write-Host "OK: $file - Thai text restored" -ForegroundColor Green
        # Show sample
        $match = [regex]::Match($fixed, '<title>(.+?)</title>')
        if ($match.Success) {
            Write-Host "   Title: $($match.Groups[1].Value)" -ForegroundColor Gray
        }
    } else {
        Write-Host "WARN: $file - no Thai detected, restoring backup" -ForegroundColor Red
        Copy-Item $backupPath $fullPath -Force
    }
}

Write-Host "`nDone." -ForegroundColor Green
