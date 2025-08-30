// project-root/scripts/cli/utils/logger.ts
let verbose = false;
export function setVerbose(enabled) {
    verbose = enabled;
}
export function logLifecycle(stage, message) {
    if (verbose)
        console.log(`[${stage}] ${message}`);
}
export function logDryRun(action, target) {
    console.log(`🧪 Dry-run: Would ${action} → ${target}`);
}
