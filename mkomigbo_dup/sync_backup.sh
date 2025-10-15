#!/usr/bin/env bash
set -e

# --- Configuration ---
MAIN_ROOT="$(cd "$(dirname "$0")" && pwd)"   # assumes script in project-root
BACKUP_PATH="../mkomigbo_dup"                 # adjust relative path from project-root to backup
BACKUP_DIR="$MAIN_ROOT/$BACKUP_PATH"
MAIN_BRANCH="main"
BACKUP_BRANCH="main"
LOGFILE="$MAIN_ROOT/logs/sync_backup.log"

# Create log folder if missing
mkdir -p "$(dirname "$LOGFILE")"

echo "=== Backup Sync: $(date) ===" >> "$LOGFILE"

# 1. Pull latest in main
cd "$MAIN_ROOT"
echo "[Main] Pulling latest ($MAIN_BRANCH)" >> "$LOGFILE"
git fetch origin "$MAIN_BRANCH"
git checkout "$MAIN_BRANCH"
git pull origin "$MAIN_BRANCH" >> "$LOGFILE" 2>&1

# 2. Sync files to backup folder (rsync exclude .git)
echo "[Sync] Copying files to backup" >> "$LOGFILE"
rsync -rlpt --delete --exclude='.git' --exclude='logs' ./ "$BACKUP_DIR/" >> "$LOGFILE" 2>&1

# 3. Commit & push changes in backup
cd "$BACKUP_DIR"
echo "[Backup] Committing changes" >> "$LOGFILE"
git add .
if git diff --quiet --cached; then
  echo "[Backup] No changes to commit" >> "$LOGFILE"
else
  git commit -m "Sync backup from main at $(date +'%Y-%m-%d %H:%M:%S')" >> "$LOGFILE" 2>&1
  git push origin "$BACKUP_BRANCH" >> "$LOGFILE" 2>&1
  echo "[Backup] Pushed backup changes" >> "$LOGFILE"
fi

echo "=== Sync Complete ===" >> "$LOGFILE"
echo "" >> "$LOGFILE"
