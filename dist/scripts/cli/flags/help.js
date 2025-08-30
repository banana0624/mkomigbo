// project-root/scripts/cli/flags/help.ts
export function handleHelp() {
    console.log(`
🛠️ CLI Options:

  --purge                   Purge unused hooks/manifests
  --restore=<filename>      Restore a file from trash
  --trash-expiry=<days>     Delete backups older than <days>
  --dry-run                 Simulate actions without executing
  --verbose                 Show lifecycle stages and actions
  --force                   Override safety checks
  --role=<role>             Filter purge by role
  --lifecycle=<stage,...>   Filter purge by lifecycle stage
  --summary-only            Show audit summary without purging
  --help                    Show this help message
`);
}
