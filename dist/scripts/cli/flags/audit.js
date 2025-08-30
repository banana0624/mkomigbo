// project-root/scripts/cli/flags/audit.ts
import fs from 'fs';
import path from 'path';
const auditLogPath = path.resolve(__dirname, '../../../logs/audit.log');
const maxSizeBytes = 1024 * 1024; // 1MB
function rotateLogIfNeeded() {
    if (fs.existsSync(auditLogPath)) {
        const stats = fs.statSync(auditLogPath);
        if (stats.size > maxSizeBytes) {
            const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
            const rotatedPath = auditLogPath.replace('.log', `-${timestamp}.log`);
            fs.renameSync(auditLogPath, rotatedPath);
            console.log(`🔄 Rotated audit log to: ${rotatedPath}`);
        }
    }
}
export function logAudit(action, target, status) {
    rotateLogIfNeeded();
    const entry = `[${new Date().toISOString()}] ${action.toUpperCase()} → ${target} → ${status}`;
    fs.appendFileSync(auditLogPath, entry + '\n');
    console.log(`📘 Audit: ${entry}`);
}
