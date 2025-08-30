// project-root/scripts/cli/flags/version.ts
import fs from 'fs';
import path from 'path';
export function showVersion() {
    try {
        const raw = fs.readFileSync(path.resolve(__dirname, '../../../package.json'), 'utf-8');
        const pkg = JSON.parse(raw);
        console.log(`CLI Version: ${pkg.version}`);
    }
    catch (error) {
        console.error('❌ Failed to read version from package.json:', error);
    }
}
