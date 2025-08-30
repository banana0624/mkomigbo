// project-root/core/registeries/lifecycleregistry.ts

// Registry for lifecycle hooks across modules
import { LifecycleHook } from './types';

export const lifecycleRegistry = {
  onInit: new Set<LifecycleHook>(),
  onDestroy: new Set<LifecycleHook>(),
  onUpdate: new Set<LifecycleHook>(),
};

// Hook registration
export function registerHook(type: keyof typeof lifecycleRegistry, hook: LifecycleHook) {
  lifecycleRegistry[type].add(hook);
}
