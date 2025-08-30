// project-root/scripts/utils/runSafe.ts
/**
 * 🔒 Safe wrapper for CLI tasks
 * Ensures graceful error handling and exit on failure
 */
export async function runSafe(taskName, fn) {
    try {
        console.log(`[${taskName}] Starting...`);
        await fn();
        console.log(`[${taskName}] Completed successfully.`);
    }
    catch (err) {
        console.error(`[${taskName}] Failed:`, err);
        process.exit(1);
    }
}
/**
 * 🔁 Retry wrapper for CLI tasks
 * Retries the task up to `retries` times before failing
 */
export async function retrySafe(taskName, fn, retries = 2) {
    for (let attempt = 1; attempt <= retries + 1; attempt++) {
        try {
            console.log(`[${taskName}] Attempt ${attempt}`);
            await fn();
            console.log(`[${taskName}] Completed successfully.`);
            return;
        }
        catch (err) {
            console.warn(`[${taskName}] Error on attempt ${attempt}:`, err);
            if (attempt > retries) {
                console.error(`[${taskName}] All retries failed.`);
                process.exit(1);
            }
        }
    }
}
