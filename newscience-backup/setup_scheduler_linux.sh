#!/bin/bash
# newScience Backup - Linux Crontab Setup
# Adds a crontab entry to run backup.py daily at 02:00
# Usage: ./setup_scheduler_linux.sh [--remove]

set -e

TOOL_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
BACKUP_SCRIPT="$TOOL_DIR/backup.py"
BACKUP_DIR="$TOOL_DIR/backups"
LOG_FILE="$TOOL_DIR/backups/scheduled_backup.log"

if [ ! -f "$BACKUP_SCRIPT" ]; then
    echo "Error: backup.py not found at $BACKUP_SCRIPT" >&2
    exit 1
fi

# Prefer python3
if command -v python3 &>/dev/null; then
    PYTHON=python3
elif command -v python &>/dev/null; then
    PYTHON=python
else
    echo "Error: python3 or python not found in PATH" >&2
    exit 1
fi

mkdir -p "$BACKUP_DIR"

# Crontab line: 0 2 * * * = daily at 02:00
CRON_LINE="0 2 * * * cd \"$TOOL_DIR\" && $PYTHON \"$BACKUP_SCRIPT\" >> \"$LOG_FILE\" 2>&1"

if [ "${1:-}" = "--remove" ]; then
    if crontab -l 2>/dev/null | grep -qF "$BACKUP_SCRIPT"; then
        (crontab -l 2>/dev/null || true) | grep -vF "$BACKUP_SCRIPT" | crontab -
        echo "Crontab entry for newScience backup removed."
    else
        echo "No crontab entry found for newScience backup."
    fi
    exit 0
fi

# Check if already installed
if crontab -l 2>/dev/null | grep -qF "$BACKUP_SCRIPT"; then
    echo "Crontab entry for newScience backup already exists."
    crontab -l | grep backup
    exit 0
fi

# Add new entry
(crontab -l 2>/dev/null || true; echo "$CRON_LINE") | crontab -
echo "Crontab entry added: daily at 02:00"
echo "  $CRON_LINE"
echo "Log file: $LOG_FILE"
echo "To remove: $0 --remove"
