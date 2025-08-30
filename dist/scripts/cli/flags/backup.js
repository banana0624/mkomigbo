// project-root/scripte/cli/flags/backup.ts
import { confirmAction, logSummary, snapshotManifest } from '@utils';
import { getManifestPath, getBackupPath } from '@paths';
export async function handleBackupFlag({ dryRun = false, summaryOnly = false }) {
    const manifestPath = getManifestPath();
    const backupPath = getBackupPath();
    if (dryRun) {
        logSummary(`Would snapshot manifest from ${manifestPath} to ${backupPath}`);
        return;
    }
    const confirmed = await confirmAction(`Snapshot manifest before destructive operation?`);
    if (!confirmed)
        return;
    await snapshotManifest(manifestPath, backupPath);
    if (!summaryOnly) {
        console.log(`✅ Backup created at ${backupPath}`);
    }
}
