@echo off
REM ================================
REM Wrapper to run sync_backup.sh with logging
REM ================================

REM Change directory to project root
cd /D "F:\xampp\htdocs\mkomigbo\project-root"

REM Path to Git Bash (adjust if necessary)
set GIT_BASH="C:\Program Files\Git\bin\bash.exe"

REM Log file path
set LOG_PATH="logs\sync_backup_wrapper.log"

REM Run the sync script via bash, redirect output & errors to log
%GIT_BASH% -lc "bash sync_backup.sh" >> %LOG_PATH% 2>&1
