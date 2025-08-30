// project-root/scripts/cli/paths.ts
import path from 'path';
export function getManifestPath() {
    return path.resolve(__dirname, '../../manifests/current.json');
}
export function getBackupPath() {
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
    return path.resolve(__dirname, `../../backups/manifest-${timestamp}.json`);
}
