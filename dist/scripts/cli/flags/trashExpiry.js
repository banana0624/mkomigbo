// project-root/scripts/cli/flags/trashExpiry.ts
import fs from 'fs';
import path from 'path';
import { deleteFile } from '../utils/fileOps';
export function handleTrashExpiry(args) {
    const arg = args.find(a => a.startsWith('--trash-expiry='));
    const days = parseInt(arg?.split('=')[1] || '0', 10);
    const dryRun = args.includes('--dry-run');
    const cutoff = Date.now() - days * 86400000;
    const files = fs.readdirSync('trash');
    for (const file of files) {
        const filePath = path.join('trash', file);
        const stats = fs.statSync(filePath);
        if (stats.mtimeMs < cutoff)
            deleteFile(filePath, dryRun);
    }
}
