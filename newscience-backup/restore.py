#!/usr/bin/env python3
"""
newScience Restore Script
Restores from a backup: database (from .sql.gz) and file archives (.zip).
Uses Python stdlib only. Prompts for confirmation unless --force.
"""

import argparse
import gzip
import json
import os
import re
import shutil
import subprocess
import sys
from datetime import datetime
from pathlib import Path
from zipfile import ZipFile

SCRIPT_DIR = Path(__file__).resolve().parent

# เดือนภาษาไทย (มกราคม–ธันวาคม)
THAI_MONTHS = (
    "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
    "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม",
)


def format_date_thai(backup_name_or_iso: str) -> str:
    """
    แปลงชื่อ backup (เช่น 2026-03-10_020000) หรือ timestamp ISO เป็นข้อความภาษาไทย:
    วันที่ เดือนไทย พ.ศ.
    """
    dt = None
    if not backup_name_or_iso:
        return ""
    # ลองจาก backup name แบบ YYYY-MM-DD_HHMMSS
    m = re.match(r"(\d{4})-(\d{2})-(\d{2})", backup_name_or_iso)
    if m:
        try:
            y, mo, d = int(m.group(1)), int(m.group(2)), int(m.group(3))
            if 1 <= mo <= 12 and 1 <= d <= 31:
                dt = datetime(y, mo, d)
        except ValueError:
            pass
    if dt is None:
        try:
            # ลอง parse เป็น ISO (ใช้แค่ส่วนวันที่)
            dt = datetime.fromisoformat(backup_name_or_iso.replace("Z", "+00:00"))
        except (ValueError, TypeError):
            return backup_name_or_iso
    day = dt.day
    month_thai = THAI_MONTHS[dt.month - 1]
    year_be = dt.year + 543  # พ.ศ. = ค.ศ. + 543
    return f"{day} {month_thai} {year_be}"


def find_mysql_binaries(config_mysql: dict):
    """Return (mysqldump_path, mysql_path). Auto-detect if not in config."""
    mysql_bin = (config_mysql.get("mysql_bin") or "").strip()
    if mysql_bin:
        return mysql_bin

    is_windows = os.name == "nt"
    ext = ".exe" if is_windows else ""
    search_dirs = []
    if is_windows:
        search_dirs = [
            Path("C:/xampp/mysql/bin"),
            Path(os.environ.get("ProgramFiles", "C:/Program Files")) / "MySQL/MySQL Server 8.0/bin",
        ]
    else:
        search_dirs = [Path("/usr/bin"), Path("/usr/local/bin")]

    def find_exe(name: str):
        if is_windows and not name.endswith(".exe"):
            name += ".exe"
        for d in search_dirs:
            p = d / name
            if p.is_file():
                return str(p)
        which = "where" if is_windows else "which"
        try:
            r = subprocess.run([which, name], capture_output=True, text=True, timeout=5)
            if r.returncode == 0 and r.stdout.strip():
                return r.stdout.strip().split("\n")[0].strip()
        except (FileNotFoundError, subprocess.TimeoutExpired):
            pass
        return ""

    return find_exe("mysql")


def load_config(config_path: Path) -> dict:
    """Load backup_config.json."""
    if not config_path.is_file():
        print(f"Error: Config not found: {config_path}", file=sys.stderr)
        sys.exit(1)
    with open(config_path, "r", encoding="utf-8") as f:
        cfg = json.load(f)
    cfg["_config_dir"] = str(config_path.parent)
    return cfg


def resolve_path(cfg: dict, key: str, base: Path) -> Path:
    """Resolve path from config (relative to config dir)."""
    raw = cfg.get(key, ".")
    p = Path(raw)
    if not p.is_absolute():
        p = base / p
    return p.resolve()


def list_backups(backup_base: Path) -> list:
    """Return list of backup dirs (newest first), each with manifest info."""
    result = []
    if not backup_base.is_dir():
        return result
    for d in sorted(backup_base.iterdir(), reverse=True):
        if not d.is_dir():
            continue
        manifest_path = d / "manifest.json"
        if not manifest_path.is_file():
            continue
        try:
            with open(manifest_path, "r", encoding="utf-8") as f:
                m = json.load(f)
            result.append({"path": d, "name": d.name, "manifest": m})
        except Exception:
            result.append({"path": d, "name": d.name, "manifest": {}})
    return result


def run_restore_db(
    backup_dir: Path,
    cfg: dict,
    force: bool,
    log_lines: list,
) -> bool:
    """Restore database from backup_dir/database.sql.gz."""
    sql_gz = backup_dir / "database.sql.gz"
    if not sql_gz.is_file():
        log_lines.append("No database.sql.gz in backup; skip DB restore")
        return True

    mysql_cfg = cfg.get("mysql", {})
    host = mysql_cfg.get("host", "localhost")
    port = mysql_cfg.get("port", 3306)
    user = mysql_cfg.get("user", "root")
    password = mysql_cfg.get("password", "")
    database = mysql_cfg.get("database", "")
    if not database:
        log_lines.append("Error: database name not set in config")
        return False

    mysql_bin = find_mysql_binaries(mysql_cfg)
    if not mysql_bin or not Path(mysql_bin).is_file():
        log_lines.append("Error: mysql client not found (set mysql.mysql_bin in config)")
        return False

    if not force:
        confirm = input(f"Restore database '{database}'? Existing data will be overwritten. [y/N]: ")
        if confirm.strip().lower() != "y":
            log_lines.append("DB restore skipped by user")
            return True

    args = [mysql_bin, "-h", str(host), "-P", str(port), "-u", user, database]
    if password:
        args.append(f"-p{password}")

    try:
        with gzip.open(sql_gz, "rb") as gz:
            proc = subprocess.Popen(
                args,
                stdin=subprocess.PIPE,
                stderr=subprocess.PIPE,
            )
            _, err = proc.communicate(input=gz.read(), timeout=3600)
            if proc.returncode != 0:
                log_lines.append(f"mysql import error: {err.decode('utf-8', errors='replace')}")
                return False
    except subprocess.TimeoutExpired:
        proc.kill()
        log_lines.append("mysql import timed out")
        return False
    except Exception as e:
        log_lines.append(f"DB restore failed: {e}")
        return False

    log_lines.append("Database restored successfully")
    return True


def run_restore_files(
    backup_dir: Path,
    project_root: Path,
    cfg: dict,
    force: bool,
    log_lines: list,
) -> bool:
    """Extract file zip archives from backup to project paths."""
    targets = cfg.get("backup_targets", {})
    for key, rel_path in targets.items():
        zip_name = f"files_{key}.zip"
        zip_path = backup_dir / zip_name
        if not zip_path.is_file():
            log_lines.append(f"Skip (missing): {zip_name}")
            continue
        dest = project_root / rel_path
        if not force:
            confirm = input(f"Extract {zip_name} -> {dest}? Existing files may be overwritten. [y/N]: ")
            if confirm.strip().lower() != "y":
                log_lines.append(f"Skipped extraction: {zip_name}")
                continue
        dest.mkdir(parents=True, exist_ok=True)
        try:
            with ZipFile(zip_path, "r") as zf:
                zf.extractall(dest)
            log_lines.append(f"Extracted {zip_name} -> {dest}")
        except Exception as e:
            log_lines.append(f"Extract failed {zip_name}: {e}")
            return False
    return True


def run_restore_env(backup_dir: Path, project_root: Path, force: bool, log_lines: list) -> bool:
    """Restore .env from backup_dir/env_backup to project_root/.env."""
    env_backup = backup_dir / "env_backup"
    if not env_backup.is_file():
        log_lines.append("No env_backup in backup; skip .env restore")
        return True
    dest = project_root / ".env"
    if not force:
        confirm = input(f"Restore .env from backup to {dest}? [y/N]: ")
        if confirm.strip().lower() != "y":
            log_lines.append(".env restore skipped by user")
            return True
    try:
        shutil.copy2(env_backup, dest)
        log_lines.append("Restored .env")
        return True
    except Exception as e:
        log_lines.append(f"Failed to restore .env: {e}")
        return False


def main():
    parser = argparse.ArgumentParser(description="newScience restore from backup (DB + files + .env)")
    parser.add_argument("backup_name", nargs="?", help="Backup directory name (e.g. 2026-03-10_020000). If omitted, list backups and prompt to choose.")
    parser.add_argument("--config", "-c", default=str(SCRIPT_DIR / "backup_config.json"), help="Path to backup_config.json")
    parser.add_argument("--list", "-l", action="store_true", help="List available backups only")
    parser.add_argument("--db-only", action="store_true", help="Restore database only")
    parser.add_argument("--files-only", action="store_true", help="Restore files and .env only")
    parser.add_argument("--force", "-f", action="store_true", help="Skip confirmation prompts")
    args = parser.parse_args()

    config_path = Path(args.config).resolve()
    cfg = load_config(config_path)
    config_dir = Path(cfg["_config_dir"])
    project_root = resolve_path(cfg, "project_path", config_dir)
    backup_base = resolve_path(cfg, "backup_path", config_dir)

    backups = list_backups(backup_base)

    if args.list:
        if not backups:
            print("No backups found.")
            return 0
        print(f"Backups in {backup_base}:")
        print(f"  {'ชื่อ backup':<24}  |  {'วันที่ (พ.ศ.)':<28}  |  {'DB':<20}  |  .env")
        print("  " + "-" * 80)
        for b in backups:
            m = b["manifest"]
            ts = m.get("timestamp", b["name"])
            date_thai = format_date_thai(ts)
            db = m.get("database", "")
            env_ok = "yes" if m.get("env_backed_up") else "no"
            print(f"  {b['name']:<24}  |  {date_thai:<28}  |  {db:<20}  |  {env_ok}")
        return 0

    backup_name = args.backup_name
    if not backup_name:
        if not backups:
            print("No backups found. Run backup.py first.")
            return 1
        print("Available backups:")
        for i, b in enumerate(backups, 1):
            m = b["manifest"]
            ts = m.get("timestamp", b["name"])
            date_thai = format_date_thai(ts)
            db = m.get("database", "")
            print(f"  {i}. {b['name']}  ({date_thai})  DB: {db}")
        try:
            choice = input("\nEnter number or backup name to restore (or Enter to quit): ").strip()
        except EOFError:
            print("No input.")
            return 0
        if not choice:
            return 0
        if choice.isdigit() and 1 <= int(choice) <= len(backups):
            backup_name = backups[int(choice) - 1]["name"]
        else:
            backup_name = choice

    backup_dir = backup_base / backup_name
    if not backup_dir.is_dir():
        print(f"Error: Backup directory not found: {backup_dir}", file=sys.stderr)
        sys.exit(1)
    manifest_path = backup_dir / "manifest.json"
    if not manifest_path.is_file():
        print("Error: Invalid backup (no manifest.json)", file=sys.stderr)
        sys.exit(1)

    # Full restore = DB + files + .env so the app works right away
    do_db = not args.files_only
    do_files = not args.db_only
    log_lines = []

    if do_db:
        if not run_restore_db(backup_dir, cfg, args.force, log_lines):
            sys.exit(1)
    if do_files:
        run_restore_files(backup_dir, project_root, cfg, args.force, log_lines)
        # Always restore .env when restoring files so the app is usable
        run_restore_env(backup_dir, project_root, args.force, log_lines)

    for line in log_lines:
        print(line)
    print("Restore completed. Application should be ready to use (DB + files + .env).")
    return 0


if __name__ == "__main__":
    sys.exit(main())
