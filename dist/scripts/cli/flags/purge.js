// project-root/scripts/cli/flags/purge.ts
import fs from 'fs';
import path from 'path';
import { confirm } from '../utils/confirm';
import { setVerbose, logLifecycle } from '../utils/logger';
import { backupToTrash, gitSafeDelete } from '../utils/fileOps';
import { filterByRoleAndLifecycle, summarizeAudit, summarizeLifecycle } from '../utils/validator';
export async function handlePurge(args) {
    const dryRun = args.includes('--dry-run');
    const verbose = args.includes('--verbose');
    const force = args.includes('--force');
    const summaryOnly = args.includes('--summary-only');
    const roleArg = args.find(arg => arg.startsWith('--role='))?.split('=')[1];
    const lifecycleArg = args.find(arg => arg.startsWith('--lifecycle='))?.split('=')[1];
    const lifecycleStages = lifecycleArg ? lifecycleArg.split(',') : undefined;
    setVerbose(verbose);
    const logsPath = path.resolve(__dirname, '../../../logs/audit-unused.json');
    if (!fs.existsSync(logsPath)) {
        console.error('❌ Audit log not found.');
        return;
    }
    const { unusedHooks, unusedManifests } = JSON.parse(fs.readFileSync(logsPath, 'utf-8'));
    const filteredHooks = filterByRoleAndLifecycle(unusedHooks, roleArg, lifecycleStages);
    const filteredManifests = filterByRoleAndLifecycle(unusedManifests, roleArg, lifecycleStages);
    const all = [...filteredHooks, ...filteredManifests];
    summarizeAudit(filteredHooks, filteredManifests);
    summarizeLifecycle(all);
    if (summaryOnly) {
        console.log('🧾 Summary-only mode: no files purged.');
        return;
    }
    if (all.length === 0) {
        console.log('✅ No matching unused files to purge.');
        return;
    }
    const confirmed = force || await confirm(`Purge ${all.length} files for role=${roleArg || 'any'}?`);
    if (!confirmed) {
        console.log('⚠️ Purge cancelled.');
        return;
    }
    for (const file of all) {
        if (fs.existsSync(file)) {
            backupToTrash(file, dryRun);
            gitSafeDelete(file, dryRun, force);
        }
    }
    logLifecycle('purge', '✅ Purge complete.');
}
