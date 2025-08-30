// project-root/scripts/utils/animation.ts

export const animatePulse = (id: string, duration = 1200): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('pulse');
  setTimeout(() => el.classList.remove('pulse'), duration);
};

export const animateFade = (id: string, direction: 'in' | 'out'): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.remove('fade-in', 'fade-out');
  el.classList.add(`fade-${direction}`);
};

/** Rhythm-preserving transitions and micro-interactions. */
export function playTransition(name: string): void { /* TODO */ }
export function registerAnimation(name: string, impl: () => void): void { /* TODO */ }

