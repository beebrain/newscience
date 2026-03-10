# newScience Backup & Restore Tool

Standalone Python scripts to backup and restore the newScience application: MySQL database and file uploads. Can be run on a schedule (Windows Task Scheduler or Linux cron).

## Requirements

- **Python 3.7+** (stdlib only; no pip install needed)
- **MySQL client tools**: `mysqldump` and `mysql` in PATH or configured in `backup_config.json`
  - Windows (XAMPP): typically `C:\xampp\mysql\bin\`
  - Linux: `apt install mysql-client` or use system MySQL

## Configuration

1. Copy and edit **`backup_config.json`**:
   - **project_path**: Path to the newScience project (where `writable/`, `public/`, `.env` live). Use `..` if this tool lives inside the project as `newscience-backup/`.
   - **backup_path**: Where to store backups (e.g. `./backups`).
   - **mysql**: Host, port, user, password, database. Optionally set `mysqldump_bin` and `mysql_bin` if the executables are not in PATH.
   - **retention.keep_last**: Number of backup sets to keep (default 7).
   - **backup_targets**: Directories to zip (relative to project_path).

2. Ensure the project has the expected structure: `writable/uploads/`, `writable/edoc_documents/`, `writable/private/`, `public/uploads/`, and `.env`.

## Backup

Run from the `newscience-backup` directory (or set `--config` to the config file path):

```bash
python backup.py
```

**Options:**

- `--config`, `-c` — Path to `backup_config.json` (default: same directory as script).
- `--db-only` — Backup only the database.
- `--files-only` — Backup only files and `.env`.
- `--keep`, `-k` — Number of backup sets to retain (overrides config).

**Output:** A new directory under `backup_path` named `YYYY-MM-DD_HHMMSS` containing:

- `database.sql.gz` — Compressed MySQL dump.
- `files_<target>.zip` — One zip per entry in `backup_targets`.
- `env_backup` — Copy of `.env` (always saved so restore gives a working app).
- `manifest.json` — Metadata (timestamp, sizes, DB name).

Backup **always stores .env** (even with `--db-only` or `--files-only`) so that after restore the application is ready to use. Old backups beyond `keep_last` are deleted automatically.

## Restore

**Run restore (interactive — no arguments needed):**

```bash
python restore.py
```

This shows the list of backups and prompts you to enter a number or backup name. After restore (DB + files + .env), the application is ready to use.

**List backups only:**

```bash
python restore.py --list
```

**Restore a specific backup:**

```bash
python restore.py 2026-03-10_020000
```

Restore always includes **.env** when restoring files, so the app works after restore.

**Options:**

- `--config`, `-c` — Path to `backup_config.json`.
- `--list`, `-l` — List available backups and exit.
- `--db-only` — Restore only the database.
- `--files-only` — Restore only files and `.env`.
- `--force`, `-f` — Skip confirmation prompts (use with care).

Restore will prompt before overwriting the database, extracting files, or restoring `.env`, unless `--force` is used.

## Scheduled backup

### Windows (Task Scheduler)

1. Open PowerShell **as Administrator**.
2. Go to the backup tool directory: `cd path\to\newscience-backup`.
3. Run: `.\setup_scheduler_windows.ps1`.
4. The task **newScience-Backup** runs daily at 02:00. Logs go to `backups\scheduled_backup.log`.
5. To remove the task: `.\setup_scheduler_windows.ps1 -Remove`.

### Linux (cron)

1. Make the script executable: `chmod +x setup_scheduler_linux.sh`.
2. Run: `./setup_scheduler_linux.sh`.
3. Backup runs daily at 02:00. Logs go to `backups/scheduled_backup.log`.
4. To remove: `./setup_scheduler_linux.sh --remove`.

## File layout

```
newscience-backup/
  backup_config.json       # Configuration
  backup.py                # Backup script
  restore.py               # Restore script
  setup_scheduler_windows.ps1
  setup_scheduler_linux.sh
  requirements.txt
  README.md
  backups/                 # Created by backup (gitignored)
    2026-03-10_020000/
      manifest.json
      database.sql.gz
      files_writable_uploads.zip
      files_edoc_documents.zip
      files_private_keys.zip
      files_public_uploads.zip
      env_backup
    backup.log
    scheduled_backup.log
```

## Notes

- Backup and restore use the **same** `backup_config.json` (same DB and paths). Ensure the config matches the environment you are restoring to.
- Keep `backup_config.json` out of version control if it contains real passwords, or use a template and document required keys.
- For restore on a fresh server: install MySQL, create the database, set `project_path` and `mysql` in config, then run `restore.py <backup_name>`.
