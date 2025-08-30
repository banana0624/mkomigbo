// scripts/onboarding/demoHarness.ts

import { initOnboardingUI } from './init';
import { goToOnboardingStep } from './overlayLogic';
import { traceOnboardingEvent, rhythmTick } from './auditHeatmap';

/**
 * Demo harness to let contributors experience onboarding + heatmap in real time.
 * Assumes the page has:
 *  - A container with id="audit-heatmap"
 *  - Buttons with ids: start-onboarding, next-step, stop-onboarding
 *  - An overlay element with id="onboarding-overlay"
 */
export const mountOnboardingDemo = () => {
  if (typeof document === 'undefined') return;

  const ui = initOnboardingUI({ echo: true, heatmapContainerId: 'audit-heatmap', paintIntervalMs: 400 });

  const startBtn = document.getElementById('start-onboarding');
  const nextBtn = document.getElementById('next-step');
  const stopBtn = document.getElementById('stop-onboarding');

  let step = 1;

  startBtn?.addEventListener('click', () => {
    ui.start(step);
    traceOnboardingEvent({
      type: 'overlay_shown',
      step,
      timestamp: Date.now(),
      rhythmIndex: rhythmTick(),
    });
  });

  nextBtn?.addEventListener('click', () => {
    step += 1;
    ui.next(step);
    traceOnboardingEvent({
      type: 'overlay_step',
      step,
      timestamp: Date.now(),
      rhythmIndex: rhythmTick(),
    });
  });

  stopBtn?.addEventListener('click', () => {
    ui.stop();
    traceOnboardingEvent({
      type: 'overlay_hidden',
      step,
      timestamp: Date.now(),
      rhythmIndex: rhythmTick(),
    });
  });

  return () => {
    ui.cleanup();
    startBtn?.replaceWith(startBtn.cloneNode(true));
    nextBtn?.replaceWith(nextBtn.cloneNode(true));
    stopBtn?.replaceWith(stopBtn.cloneNode(true));
  };
};

// Auto-mount in dev if desired
if (typeof window !== 'undefined' && (window as any).__ONBOARDING_DEMO_AUTO_MOUNT__) {
  mountOnboardingDemo();
}
