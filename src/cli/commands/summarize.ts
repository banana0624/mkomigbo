// project-root/src/cli/commands/summarize.ts

import { summarizeBackups } from '../../backups/summarizeBackups';
import { summarizeBackupsDryRun } from '../../backups/summarizeDryRun';
import { BackupSummary } from '../../backups/types';

const args = process.argv.slice(2);
const dryRun = args.includes('--dry-run');
const summaryOnly = args.includes('--summary-only');

function printSummary(summary: BackupSummary) {
  if (summaryOnly) {
    console.log(`[SUMMARY-ONLY] ${summary.total} backups, latest: ${summary.latest}, size: ${summary.sizeEstimate}`);
  } else {
    console.log(`Total backups: ${summary.total}`);
    console.log(`Latest backup: ${summary.latest}`);
    console.log(`Size estimate: ${summary.sizeEstimate}`);
  }
}

(async () => {
  const summary = dryRun
    ? await summarizeBackupsDryRun()
    : await summarizeBackups();

  printSummary(summary);
})();
