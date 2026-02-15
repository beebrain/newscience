# Cleanup script for production deployment

Write-Host "=== Cleaning up for production deployment ==="

# Remove development and test files
Write-Host "Removing development files..."

# Test files
Remove-Item "scripts\test_db_connection.php" -Force -ErrorAction SilentlyContinue
Remove-Item "scripts\test_mysql_connection.php" -Force -ErrorAction SilentlyContinue
Remove-Item "test_personnel_import.php" -Force -ErrorAction SilentlyContinue

# Development scripts
Remove-Item "scripts\check_admin_login.php" -Force -ErrorAction SilentlyContinue
Remove-Item "scripts\set_admin_credentials.php" -Force -ErrorAction SilentlyContinue
Remove-Item "scripts\set_password_pisit.php" -Force -ErrorAction SilentlyContinue

# Old log files (keep last 7 days)
Get-ChildItem "writable\logs\*.log" | Where-Object { $_.CreationTime -lt (Get-Date).AddDays(-7) } | Remove-Item -Force

# Clear caches
Remove-Item "writable\cache\*" -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item "writable\session\*" -Recurse -Force -ErrorAction SilentlyContinue

# Remove any temporary files
Get-ChildItem -Path "." -Name "*.tmp" -Recurse | Remove-Item -Force -ErrorAction SilentlyContinue
Get-ChildItem -Path "." -Name "*.temp" -Recurse | Remove-Item -Force -ErrorAction SilentlyContinue
Get-ChildItem -Path "." -Name ".DS_Store" -Recurse | Remove-Item -Force -ErrorAction SilentlyContinue

# Set proper permissions
Write-Host "Setting permissions..."
# Note: Windows permissions are different, these are basic settings
Get-ChildItem "writable" -Recurse | Where-Object { $_.PsIsContainer } | ForEach-Object { 
    try { $_.Attributes = $_.Attributes -bor [System.IO.FileAttributes]::Normal } catch { }
}

Write-Host "=== Cleanup complete ==="
Write-Host "Ready for production deployment!"
