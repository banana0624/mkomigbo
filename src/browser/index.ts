// project-root/src/browser/index.ts

import { renderBackupTimeline } from './components/BackupTimeline';

async function fetchSummary(dryRun: boolean) {
  const res = await fetch(`/summary?dryRun=${dryRun}`);
  return await res.json();
}

(async () => {
  const container = document.getElementById('timeline');
  const summary = await fetchSummary(false); // or true for dry-run
  renderBackupTimeline(container!, summary.backups);
})();