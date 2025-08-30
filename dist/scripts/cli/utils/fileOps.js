// project-root/scripts/cli/utils/fileOps.ts
import { spawnSync } from 'child_process';
import fs from 'fs';
import path from 'path';
import { logDryRun, logLifecycle } from './logger';
export function gitSafeDelete(file, dryRun = false, force = false) {
    if (dryRun) {
        logDryRun('git rm', file);
        return;
    }
    if (!fs.existsSync(file)) {
        if (!force) {
            console.log(`❌ File not found: ${file}`);
            return;
        }
    }
    const result = spawnSync('git', ['rm', file]);
    if (result.status === 0) {
        logLifecycle('delete', `Git-removed: ${file}`);
    }
    else {
        fs.unlinkSync(file);
        logLifecycle('delete', `File removed (fallback): ${file}`);
    }
}
export function backupToTrash(file, dryRun = false) {
    const trashDir = path.resolve(__dirname, '../../../trash');
    if (dryRun) {
        console.log(`🧪 Dry-run: Would backup ${file} to trash`);
        return;
    }
    if (!fs.existsSync(trashDir))
        fs.mkdirSync(trashDir);
    const dest = path.join(trashDir, path.basename(file));
    fs.copyFileSync(file, dest);
    console.log(`📦 Backed up to trash: ${file}`);
}
