// project-root/scripts/onboarding/init.ts

import { initAuditTracer, onAuditEvent, paintHeatmapRhythm, setOnboardingEventMirror } from './auditHeatmap';
import { mountOnboardingOverlay, unmountOnboardingOverlay, goToOnboardingStep } from './overlayLogic';
import { pushFeedback } from '../utils/feedback';
import { createHeatmapGrid } from './heatmapGrid';

/**
 * Initialize onboarding UI wiring:
 * - Starts audit tracer (optional console echo)
 * - Mirrors onboarding events into feedback messages
 * - Animates heatmap rhythm in the given container
 *
 * Returns a cleanup function to stop timers and detach hooks.
 */
export const initOnboardingUI = (opts?: {
  echo?: boolean;
  heatmapContainerId?: string;
  paintIntervalMs?: number;
}) => {
  const echo = !!opts?.echo;
  const containerId = opts?.heatmapContainerId ?? 'audit-heatmap';
  const paintIntervalMs = opts?.paintIntervalMs ?? 400;

  initAuditTracer({ consoleEcho: echo });

  // Mirror audit events into contributor-facing feedback messages
  setOnboardingEventMirror((evt) => {
    if (evt.type === 'badge_awarded' && evt.milestone) {
      pushFeedback(`Badge awarded: ${evt.milestone}`, 'success', 'onboarding');
    } else if (evt.type === 'overlay_shown') {
      pushFeedback(`Onboarding started at step ${evt.step ?? 1}`, 'info', 'onboarding');
    } else if (evt.type === 'overlay_hidden') {
      pushFeedback('Onboarding dismissed', 'warning', 'onboarding');
    } else if (evt.type === 'overlay_step' && typeof evt.step === 'number') {
      pushFeedback(`Moved to step ${evt.step}`, 'info', 'onboarding');
    }
  });

  const grid = createHeatmapGrid({
    containerId, // should match the ID of your heatmap container
    rows: 6,
    cols: 8,
    decayMs: 2000,
    palette: 'warm',
  });
  

  // Keep heatmap container in sync with rhythm ticks
  const intervalId = setInterval(() => {
    paintHeatmapRhythm(containerId);
  }, paintIntervalMs);

  // Example public controls for consumers
  const start = (initialStep = 1) => mountOnboardingOverlay(initialStep);
  const next = (step: number) => goToOnboardingStep(step);
  const stop = () => unmountOnboardingOverlay();

  // Optional: echo subscription lifecycles
  const unsubscribe = onAuditEvent(() => { /* no-op by default */ });

  const cleanup = () => {
    clearInterval(intervalId);
    unsubscribe();
    setOnboardingEventMirror(null);
  };

  return { start, next, stop, cleanup };
};
