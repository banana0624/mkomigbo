// project-root/scripts/utils/lifecycle.ts

/** Lifecycle hooks to keep rhythm across navigation and overlays. */
export function onAppStart(cb: () => void) { /* TODO */ }
export function onRouteChange(cb: (route: string) => void) { /* TODO */ }

export const isDOMReady = (): boolean =>
  typeof document !== 'undefined' && document.readyState !== 'loading';

export const onDOMReady = (fn: () => void): void => {
  if (isDOMReady()) {
    fn();
  } else {
    document.addEventListener('DOMContentLoaded', fn, { once: true });
  }
};
