// project-root/scripts/onboarding/overlayLogic.ts

import { triggerContributorBadge } from './contributorBadgeTrigger';
import { fadeIn, fadeOut } from './animations';
import { traceOnboardingEvent, rhythmTick } from './auditHeatmap';

const OVERLAY_ID = 'onboarding-overlay';

let currentStep = 1;
let mounted = false;

const getOverlay = (): HTMLElement | null =>
  typeof document === 'undefined' ? null : (document.getElementById(OVERLAY_ID) as HTMLElement | null);

export const mountOnboardingOverlay = (initialStep = 1): void => {
  if (mounted) return;
  currentStep = Math.max(1, initialStep);
  const overlay = getOverlay();
  if (!overlay) return;

  overlay.setAttribute('aria-live', 'polite');
  renderStep(currentStep);
  fadeIn(OVERLAY_ID);
  mounted = true;

  traceOnboardingEvent({
    type: 'overlay_shown',
    step: currentStep,
    timestamp: Date.now(),
    rhythmIndex: rhythmTick(),
  });
};

export const unmountOnboardingOverlay = (): void => {
  if (!mounted) return;
  const overlay = getOverlay();
  if (overlay) fadeOut(OVERLAY_ID);
  mounted = false;

  traceOnboardingEvent({
    type: 'overlay_hidden',
    step: currentStep,
    timestamp: Date.now(),
    rhythmIndex: rhythmTick(),
  });
};

export const goToOnboardingStep = (step: number): void => {
  const overlay = getOverlay();
  if (!overlay || !mounted) return;

  currentStep = Math.max(1, step);
  renderStep(currentStep);

  traceOnboardingEvent({
    type: 'overlay_step',
    step: currentStep,
    timestamp: Date.now(),
    rhythmIndex: rhythmTick(),
  });

  // Example milestone trigger on step 3
  if (currentStep === 3) {
    triggerContributorBadge('first-commit');
    traceOnboardingEvent({
      type: 'badge_awarded',
      step: currentStep,
      milestone: 'first-commit',
      timestamp: Date.now(),
      rhythmIndex: rhythmTick(),
    });
  }
};

const renderStep = (step: number): void => {
  const overlay = getOverlay();
  if (!overlay) return;
  overlay.innerHTML = `
    <div class="onboarding-step" data-step="${step}">
      <h3>Welcome to step ${step}</h3>
      <p>Follow the guidance to keep your momentum alive.</p>
    </div>
  `;
};


