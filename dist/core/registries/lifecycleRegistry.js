// project-root/core/registeries/lifecycleregistry.ts
export const lifecycleRegistry = {
    onInit: new Set(),
    onDestroy: new Set(),
    onUpdate: new Set(),
};
// Hook registration
export function registerHook(type, hook) {
    lifecycleRegistry[type].add(hook);
}
