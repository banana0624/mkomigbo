// project-root/scripts/registry/traceHooks.ts

import { log } from '../utils/log';

type LifecycleEvent = 'onInit' | 'onRender' | 'onDestroy' | 'onValidate' | 'onMerge';

interface HookContext {
  module: string;
  role?: string;
  [key: string]: any;
}

type HookFn = (context: HookContext) => void | Promise<void>;

const registry: Record<LifecycleEvent, HookFn[]> = {
  onInit: [],
  onRender: [],
  onDestroy: [],
  onValidate: [],
  onMerge: []
};

export function registerHook(event: LifecycleEvent, fn: HookFn) {
  if (!registry[event]) {
    throw new Error(`Invalid lifecycle event: ${event}`);
  }
  registry[event].push(fn);
  log.verbose(`Registered hook for ${event}`, true);
}

export async function triggerHooks(event: LifecycleEvent, context: HookContext) {
  const hooks = registry[event];
  if (!hooks || hooks.length === 0) {
    log.verbose(`No hooks registered for ${event}`, true);
    return;
  }

  for (const hookFn of hooks) {
    try {
      await hookFn(context);
      log.trace(event, context);
    } catch (err) {
      log.error(`Hook error in ${event}: ${(err as Error).message}`);
    }
  }
}