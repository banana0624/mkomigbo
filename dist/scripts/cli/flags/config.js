// project-root/scripts/cli/flags/congig.ts
import fs from 'fs';
export function loadConfig(path) {
    if (!fs.existsSync(path)) {
        throw new Error(`Config file not found at ${path}`);
    }
    return JSON.parse(fs.readFileSync(path, 'utf-8'));
}
