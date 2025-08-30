// project-root/scripts/registry/traceHooks.ts
import { log } from '../utils/log';
const registry = {
    onInit: [],
    onRender: [],
    onDestroy: [],
    onValidate: [],
    onMerge: []
};
export function registerHook(event, fn) {
    if (!registry[event]) {
        throw new Error(`Invalid lifecycle event: ${event}`);
    }
    registry[event].push(fn);
    log.verbose(`Registered hook for ${event}`, true);
}
export async function triggerHooks(event, context) {
    const hooks = registry[event];
    if (!hooks || hooks.length === 0) {
        log.verbose(`No hooks registered for ${event}`, true);
        return;
    }
    for (const hookFn of hooks) {
        try {
            await hookFn(context);
            log.trace(event, context);
        }
        catch (err) {
            log.error(`Hook error in ${event}: ${err.message}`);
        }
    }
}
