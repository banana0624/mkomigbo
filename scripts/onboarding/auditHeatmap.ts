// projecct-root/scripts/onboarding/auditHeatmap.ts

import type { OnboardingEvent, OnboardingEventType } from '../types';

type Subscriber = (evt: OnboardingEvent) => void;
type Mirror = (evt: OnboardingEvent) => void;

const subscribers: Set<Subscriber> = new Set();
const buffer: OnboardingEvent[] = [];

let consoleEcho = false;
let rhythmBase = Date.now();
let mirror: Mirror | null = null;

/**
 * Initialize tracer behavior. Can be called multiple times safely.
 */
export const initAuditTracer = (opts?: { consoleEcho?: boolean; rhythmBaseMs?: number }): void => {
  consoleEcho = !!opts?.consoleEcho;
  if (typeof opts?.rhythmBaseMs === 'number') rhythmBase = opts.rhythmBaseMs;
};

/**
 * Optional mirror to forward events to other systems (e.g., feedback).
 */
export const setOnboardingEventMirror = (fn: Mirror | null): void => {
  mirror = fn;
};

/**
 * Compute a lightweight rhythm index from elapsed time since rhythmBase.
 */
export const rhythmTick = (): number => {
  const elapsed = Date.now() - rhythmBase;
  return Math.floor(elapsed / 400) % 8;
};

/**
 * Core trace function: buffers, notifies, and optionally echoes to console.
 */
export const traceOnboardingEvent = (
  evt: Omit<OnboardingEvent, 'timestamp' | 'rhythmIndex'> & { timestamp?: number; rhythmIndex?: number }
): void => {
  const event: OnboardingEvent = {
    ...evt, // preserves evt.field, evt.status, step, milestone, etc.
    timestamp: evt.timestamp ?? Date.now(),
    rhythmIndex: typeof evt.rhythmIndex === 'number' ? evt.rhythmIndex : rhythmTick(),
    type: evt.type as OnboardingEventType,
  };
  buffer.push(event);
  subscribers.forEach((fn) => fn(event));
  if (mirror) mirror(event);
  if (consoleEcho) {
    // eslint-disable-next-line no-console
    console.log(`[audit] ${event.type}`, {
      step: (event as any).step,
      milestone: (event as any).milestone,
      field: (event as any).field,
      status: (event as any).status,
      rhythm: event.rhythmIndex,
      ts: event.timestamp,
    });
  }
};

/**
 * Subscribe to audit events (e.g., heatmap painter).
 */
export const onAuditEvent = (fn: Subscriber): () => void => {
  subscribers.add(fn);
  return () => subscribers.delete(fn);
};

/**
 * Read-only snapshot for external renderers.
 */
export const getAuditBuffer = (): readonly OnboardingEvent[] => buffer;

/**
 * Example: naive heatmap painter that toggles rhythm classes.
 * In real UI, replace with your canvas/SVG renderer.
 */
export const paintHeatmapRhythm = (containerId: string): void => {
  if (typeof document === 'undefined') return;
  const el = document.getElementById(containerId);
  if (!el) return;
  const tick = rhythmTick();
  const classes = Array.from({ length: 8 }, (_, i) => `rhythm-${i}`);
  el.classList.remove(...classes);
  el.classList.add(`rhythm-${tick}`);
};
