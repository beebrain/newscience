#!/usr/bin/env python3
"""
newScience Backup Script
Backs up MySQL database (mysqldump + gzip), file targets (zip), and .env so that
restore.py can restore everything and the app is ready to use.
Uses Python stdlib only. Run from newscience-backup directory or set --config.
"""

import argparse
import fnmatch
import gzip
import json
import os
import shutil
import subprocess
import sys
from datetime import datetime
from pathlib import Path
from zipfile import ZipFile, ZIP_DEFLATED

# Script directory (where backup_config.json lives when using default)
SCRIPT_DIR = Path(__file__).resolve().parent


def find_mysql_binaries(config_mysql: dict):
    """Return (mysqldump_path, mysql_path). Auto-detect if not in config."""
    dump_bin = (config_mysql.get("mysqldump_bin") or "").strip()
    mysql_bin = (config_mysql.get("mysql_bin") or "").strip()
    if dump_bin and mysql_bin:
        return dump_bin, mysql_bin

    # Common paths to try (Windows XAMPP, Linux)
    is_windows = os.name == "nt"
    ext = ".exe" if is_windows else ""
    search_dirs = []
    if is_windows:
        search_dirs = [
            Path("C:/xampp/mysql/bin"),
            Path(os.environ.get("ProgramFiles", "C:/Program Files")) / "MySQL/MySQL Server 8.0/bin",
        ]
    else:
        search_dirs = [
            Path("/usr/bin"),
            Path("/usr/local/bin"),
        ]

    def find_exe(name: str) -> str:
        if is_windows and not name.endswith(".exe"):
            name += ".exe"
        for d in search_dirs:
            p = d / name
            if p.is_file():
                return str(p)
        # Try PATH
        which = "where" if is_windows else "which"
        try:
            r = subprocess.run([which, name], capture_output=True, text=True, timeout=5)
            if r.returncode == 0 and r.stdout.strip():
                return r.stdout.strip().split("\n")[0].strip()
        except (FileNotFoundError, subprocess.TimeoutExpired):
            pass
        return ""

    if not dump_bin:
        dump_bin = find_exe("mysqldump")
    if not mysql_bin:
        mysql_bin = find_exe("mysql")
    return dump_bin, mysql_bin


def load_config(config_path: Path) -> dict:
    """Load and validate backup_config.json."""
    if not config_path.is_file():
        print(f"Error: Config not found: {config_path}", file=sys.stderr)
        sys.exit(1)
    with open(config_path, "r", encoding="utf-8") as f:
        cfg = json.load(f)
    cfg["_config_dir"] = str(config_path.parent)
    return cfg


def resolve_path(cfg: dict, key: str, base: Path) -> Path:
    """Resolve a path from config; if relative, it's relative to config dir."""
    raw = cfg.get(key, ".")
    p = Path(raw)
    if not p.is_absolute():
        p = base / p
    return p.resolve()


def run_backup_db(cfg: dict, backup_dir: Path, log_lines: list) -> dict | None:
    """Dump database to backup_dir/database.sql.gz. Returns manifest fragment or None on failure."""
    mysql_cfg = cfg.get("mysql", {})
    host = mysql_cfg.get("host", "localhost")
    port = mysql_cfg.get("port", 3306)
    user = mysql_cfg.get("user", "root")
    password = mysql_cfg.get("password", "")
    database = mysql_cfg.get("database", "")
    if not database:
        log_lines.append("Skip DB: database name not set in config")
        return None

    mysqldump_bin, _ = find_mysql_binaries(mysql_cfg)
    if not mysqldump_bin or not Path(mysqldump_bin).is_file():
        log_lines.append(f"Skip DB: mysqldump not found (set mysql.mysqldump_bin in config)")
        return None

    out_sql_gz = backup_dir / "database.sql.gz"
    args = [
        mysqldump_bin,
        "-h", str(host),
        "-P", str(port),
        "-u", user,
        "--single-transaction",
        "--routines",
        "--triggers",
        database,
    ]
    if password:
        args.append(f"-p{password}")

    try:
        with open(out_sql_gz, "wb") as out_file:
            proc_dump = subprocess.Popen(
                args,
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
            )
            with gzip.open(out_file, "wb") as gz:
                for chunk in iter(lambda: proc_dump.stdout.read(65536), b""):
                    gz.write(chunk)
            proc_dump.stdout.close()
            err = proc_dump.stderr.read().decode("utf-8", errors="replace")
            proc_dump.wait()
            if proc_dump.returncode != 0:
                log_lines.append(f"mysqldump error: {err}")
                out_sql_gz.unlink(missing_ok=True)
                return None
    except Exception as e:
        log_lines.append(f"DB backup failed: {e}")
        out_sql_gz.unlink(missing_ok=True)
        return None

    size = out_sql_gz.stat().st_size
    size_mb = round(size / (1024 * 1024), 2)
    log_lines.append(f"DB backup OK: {out_sql_gz.name} ({size_mb} MB)")
    return {"database": database, "db_dump_size_bytes": size, "db_dump_size": f"{size_mb} MB"}


def should_exclude(name: str, exclude_patterns: list) -> bool:
    """Check if file/dir name matches any exclude pattern (glob-style)."""
    for pat in exclude_patterns:
        if fnmatch.fnmatch(name, pat):
            return True
    return False


def zip_directory(
    zip_path: Path,
    source_dir: Path,
    exclude_patterns: list,
    log_lines: list,
) -> tuple[int, int]:
    """Zip source_dir into zip_path. Returns (file_count, total_bytes)."""
    if not source_dir.is_dir():
        log_lines.append(f"Skip (not a directory): {source_dir}")
        return 0, 0

    count = 0
    total_size = 0
    try:
        with ZipFile(zip_path, "w", ZIP_DEFLATED) as zf:
            for root, _dirs, files in os.walk(source_dir):
                root_path = Path(root)
                for f in files:
                    if should_exclude(f, exclude_patterns):
                        continue
                    full = root_path / f
                    try:
                        arcname = full.relative_to(source_dir)
                        zf.write(full, arcname)
                        count += 1
                        total_size += full.stat().st_size
                    except Exception as e:
                        log_lines.append(f"  Warning: {full}: {e}")
    except Exception as e:
        log_lines.append(f"Zip failed for {source_dir}: {e}")
        if zip_path.is_file():
            zip_path.unlink(missing_ok=True)
        return 0, 0

    size_mb = round(total_size / (1024 * 1024), 2)
    log_lines.append(f"Zipped {source_dir.name}: {count} files, {size_mb} MB -> {zip_path.name}")
    return count, total_size


def run_backup_files(cfg: dict, project_root: Path, backup_dir: Path, log_lines: list) -> dict:
    """Backup file targets to zip files in backup_dir. Returns manifest fragment."""
    targets = cfg.get("backup_targets", {})
    exclude = cfg.get("exclude_patterns", [])
    manifest_files = {}

    for key, rel_path in targets.items():
        source = project_root / rel_path
        zip_name = f"files_{key}.zip"
        zip_path = backup_dir / zip_name
        count, total_size = zip_directory(zip_path, source, exclude, log_lines)
        manifest_files[key] = {"count": count, "size_bytes": total_size, "size": f"{round(total_size / (1024 * 1024), 2)} MB"}
    return manifest_files


def run_backup_env(project_root: Path, backup_dir: Path, log_lines: list) -> bool:
    """Copy .env to backup_dir/env_backup (required for restore to work)."""
    env_src = project_root / ".env"
    env_dst = backup_dir / "env_backup"
    if not env_src.is_file():
        log_lines.append("No .env file to backup")
        return False
    try:
        shutil.copy2(env_src, env_dst)
        log_lines.append("Backed up .env -> env_backup")
        return True
    except Exception as e:
        log_lines.append(f"Failed to backup .env: {e}")
        return False


def write_manifest(backup_dir: Path, manifest_data: dict):
    """Write manifest.json into backup_dir."""
    path = backup_dir / "manifest.json"
    with open(path, "w", encoding="utf-8") as f:
        json.dump(manifest_data, f, indent=2, ensure_ascii=False)


def apply_retention(backup_base: Path, keep_last: int, log_lines: list):
    """Remove backup directories beyond keep_last (oldest first)."""
    if keep_last < 1:
        return
    dirs = sorted([d for d in backup_base.iterdir() if d.is_dir() and (d / "manifest.json").is_file()], reverse=True)
    to_remove = dirs[keep_last:]
    for d in to_remove:
        try:
            shutil.rmtree(d)
            log_lines.append(f"Retention: removed old backup {d.name}")
        except Exception as e:
            log_lines.append(f"Retention: failed to remove {d}: {e}")


def main():
    parser = argparse.ArgumentParser(description="newScience backup: database + files")
    parser.add_argument("--config", "-c", default=str(SCRIPT_DIR / "backup_config.json"), help="Path to backup_config.json")
    parser.add_argument("--db-only", action="store_true", help="Backup database only")
    parser.add_argument("--files-only", action="store_true", help="Backup files only")
    parser.add_argument("--keep", "-k", type=int, default=None, help="Number of backups to keep (overrides config)")
    args = parser.parse_args()

    config_path = Path(args.config).resolve()
    cfg = load_config(config_path)
    config_dir = Path(cfg["_config_dir"])
    project_root = resolve_path(cfg, "project_path", config_dir)
    backup_base = resolve_path(cfg, "backup_path", config_dir)
    keep_last = args.keep if args.keep is not None else (cfg.get("retention", {}).get("keep_last", 7))

    do_db = not args.files_only
    do_files = not args.db_only

    timestamp = datetime.now().strftime("%Y-%m-%d_%H%M%S")
    backup_dir = backup_base / timestamp
    backup_dir.mkdir(parents=True, exist_ok=True)

    log_lines = [f"[{datetime.now().isoformat()}] Backup started -> {backup_dir}"]
    manifest_data = {
        "timestamp": datetime.now().isoformat(),
        "platform": sys.platform,
        "project_path": str(project_root),
        "backup_dir": str(backup_dir),
    }

    if do_db:
        db_manifest = run_backup_db(cfg, backup_dir, log_lines)
        if db_manifest:
            manifest_data.update(db_manifest)
    if do_files:
        manifest_data["files"] = run_backup_files(cfg, project_root, backup_dir, log_lines)
    # Always backup .env so restore gives a working app
    env_ok = run_backup_env(project_root, backup_dir, log_lines)
    manifest_data["env_backed_up"] = env_ok

    write_manifest(backup_dir, manifest_data)
    apply_retention(backup_base, keep_last, log_lines)

    log_lines.append(f"Backup completed: {backup_dir}")
    for line in log_lines:
        print(line)

    # Append to backup log file
    log_file = backup_base / "backup.log"
    try:
        with open(log_file, "a", encoding="utf-8") as f:
            f.write("\n".join(log_lines) + "\n")
    except Exception:
        pass

    return 0


if __name__ == "__main__":
    sys.exit(main())
