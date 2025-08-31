// project-root/src/ui/onboarding/useOnboardingFlags.ts

import type { OnboardingFlags } from './OnboardingOverlay.js';
import { coerceOnboardingFlags } from './flags.guard.js';

let devFlags: OnboardingFlags | null = null;

try {
  // Runtime config or API injection
  const globalFlags = (window as any).__ONBOARDING_FLAGS__;
  if (globalFlags) {
    devFlags = coerceOnboardingFlags(globalFlags);
  } else {
    // Dev fallback
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore
    const json = (await import('../../../.copilot/onboarding/flags.json', { assert: { type: 'json' } })).default;
    devFlags = coerceOnboardingFlags(json);
  }
} catch {
  devFlags = null;
}

export function useOnboardingFlags(fallback?: OnboardingFlags): OnboardingFlags {
  const base = devFlags ?? fallback ?? {
    showOnboardingOverlay: false,
    badges: { jsExtensionCompliance: 100 },
    tips: [],
    themeTokenAudit: 'pending',
    directoryClarityScore: 'moderate',
    badgePulse: false,
  };
  return coerceOnboardingFlags(base);
}
