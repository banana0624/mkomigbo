// project-root/scripts/utils/navigation.ts

/** Gets the current route path. */
export function getCurrentRoute(): string {
  if (typeof window !== 'undefined') {
    return window.location.pathname;
  }
  return '/';
}

/** Navigates to a given route path. */
export function goToRoute(path: string): void {
  if (typeof window !== 'undefined') {
    window.location.href = path;
  }
}

/**
 * Registers a callback for route changes.
 * Useful for audit heatmap tracing or onboarding overlays.
 */
export function onRouteChange(cb: (route: string) => void): void {
  if (typeof window === 'undefined') return;

  const handler = () => cb(window.location.pathname);
  window.addEventListener('popstate', handler);
  window.addEventListener('pushstate', handler); // for custom router support

  // Optional: return cleanup function
  // return () => {
  //   window.removeEventListener('popstate', handler);
  //   window.removeEventListener('pushstate', handler);
  // };
}
