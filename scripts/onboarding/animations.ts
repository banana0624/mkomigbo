// project-root/scripts/onboarding/animations.ts

export type BadgeAnimation = 'pulse' | 'glow' | 'bounce';

const withElement = (id: string, fn: (el: HTMLElement) => void): void => {
  if (typeof document === 'undefined') return;
  const el = document.getElementById(id);
  if (el) fn(el);
};

export const runBadgeAnimation = (elementId: string, variant: BadgeAnimation = 'pulse', ms = 1200): void => {
  withElement(elementId, (el) => {
    const classes = ['badge-anim-pulse', 'badge-anim-glow', 'badge-anim-bounce'];
    el.classList.remove(...classes);
    const className =
      variant === 'glow' ? 'badge-anim-glow' : variant === 'bounce' ? 'badge-anim-bounce' : 'badge-anim-pulse';
    el.classList.add(className);
    setTimeout(() => el.classList.remove(className), ms);
  });
};

// Optional helpers for overlays
export const fadeIn = (id: string): void =>
  withElement(id, (el) => {
    el.classList.remove('fade-out');
    el.classList.add('fade-in');
  });

export const fadeOut = (id: string): void =>
  withElement(id, (el) => {
    el.classList.remove('fade-in');
    el.classList.add('fade-out');
  });
