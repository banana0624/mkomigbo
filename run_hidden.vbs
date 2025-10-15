Set WshShell = CreateObject("WScript.Shell")
WshShell.Run Chr(34) & "run_backup_sync.bat" & Chr(34), 0, False
Set WshShell = Nothing