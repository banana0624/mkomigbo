// project-root/core/registries/lifecycleRegistry.ts

// Registry for lifecycle hooks across modules (ESM / NodeNext friendly)
import type { LifecycleEvent, LifecycleHook } from "./types.js";

/** In-memory registry of hooks keyed by lifecycle event */
export const lifecycleRegistry: Record<LifecycleEvent, Set<LifecycleHook>> = {
  onInit: new Set(),
  onDestroy: new Set(),
  onUpdate: new Set(),
};

/**
 * Register a lifecycle hook.
 * Returns an unsubscribe function you can call to remove it.
 */
export function registerHook<T extends LifecycleEvent>(
  type: T,
  hook: LifecycleHook
): () => boolean {
  lifecycleRegistry[type].add(hook);
  return () => lifecycleRegistry[type].delete(hook);
}

/** Run all hooks of a given lifecycle type (awaits async hooks) */
export async function runHooks<T extends LifecycleEvent>(
  type: T
): Promise<void> {
  for (const fn of lifecycleRegistry[type]) {
    await fn();
  }
}
