// project-root/scripts/cli/flags/restore.ts
import { moveFile } from '../utils/fileOps';
import path from 'path';
export function handleRestore(args) {
    const arg = args.find(a => a.startsWith('--restore='));
    const filename = arg?.split('=')[1];
    const dryRun = args.includes('--dry-run');
    if (!filename)
        return console.log('❌ Missing filename for --restore');
    const src = path.join('trash', filename);
    const dest = path.join('scripts/hooks', filename);
    moveFile(src, dest, dryRun);
}
