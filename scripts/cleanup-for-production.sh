#!/bin/bash
# Cleanup script for production deployment

echo "=== Cleaning up for production deployment ==="

# Remove development and test files
echo "Removing development files..."

# Test files
rm -f scripts/test_db_connection.php
rm -f scripts/test_mysql_connection.php
rm -f test_personnel_import.php

# Development scripts
rm -f scripts/check_admin_login.php
rm -f scripts/set_admin_credentials.php
rm -f scripts/set_password_pisit.php

# Old log files (keep last 7 days)
find writable/logs -name "*.log" -mtime +7 -delete

# Clear caches
rm -rf writable/cache/*
rm -rf writable/session/*

# Remove any temporary files
find . -name "*.tmp" -delete
find . -name "*.temp" -delete
find . -name ".DS_Store" -delete

# Set proper permissions
echo "Setting permissions..."
chmod -R 755 writable/
chmod -R 755 public/uploads/

echo "=== Cleanup complete ==="
echo "Ready for production deployment!"
