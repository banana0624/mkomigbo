// project-root/scripts/cli/utils.ts
import readline from 'readline';
import fs from 'fs';
export async function confirmAction(message) {
    const rl = readline.createInterface({
        input: process.stdin,
        output: process.stdout
    });
    return new Promise(resolve => {
        rl.question(`${message} (y/N): `, answer => {
            rl.close();
            resolve(answer.trim().toLowerCase() === 'y');
        });
    });
}
export function logSummary(message) {
    console.log(`🔍 Summary: ${message}`);
}
export async function snapshotManifest(sourcePath, backupPath) {
    await fs.promises.copyFile(sourcePath, backupPath);
}
