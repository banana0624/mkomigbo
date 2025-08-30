// project-root/scripts/onboarding/demoPage.ts

import { mountOnboardingDemo } from './demoHarness';
import { mountToastContainer } from '../utils/feedbackRenderer';

export const mountDemoPage = () => {
  if (typeof document === 'undefined') return () => {};
  mountToastContainer();
  const cleanup = mountOnboardingDemo();
  return cleanup;
};

// Auto-mount when DOM is ready
if (typeof document !== 'undefined') {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => mountDemoPage());
  } else {
    mountDemoPage();
  }
}
