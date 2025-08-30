// project-root/scripts/cli/flags/validate.ts
import fs from 'fs';
// ✅ No duplicate showVersion declaration here
export function loadConfig(path) {
    if (!fs.existsSync(path)) {
        throw new Error(`Config file not found at ${path}`);
    }
    return JSON.parse(fs.readFileSync(path, 'utf-8'));
}
// ...rest of your code remains unchanged
