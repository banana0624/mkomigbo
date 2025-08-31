// project-root/src/ui/onboarding/flags.guard.ts

import type { OnboardingFlags } from './OnboardingOverlay.js';

export function isOnboardingFlags(x: any): x is OnboardingFlags {
  return !!x
    && typeof x.showOnboardingOverlay === 'boolean'
    && x.badges && typeof x.badges.jsExtensionCompliance === 'number'
    && Array.isArray(x.tips);
}

export function coerceOnboardingFlags(raw: any): OnboardingFlags {
  const def: OnboardingFlags = {
    showOnboardingOverlay: false,
    badges: { jsExtensionCompliance: 100 },
    tips: [],
    themeTokenAudit: 'pending',
    directoryClarityScore: 'moderate',
    badgePulse: false,
  };
  if (!raw || typeof raw !== 'object') return def;

  const compliance = typeof raw?.badges?.jsExtensionCompliance === 'number'
    ? raw.badges.jsExtensionCompliance : def.badges.jsExtensionCompliance;

  const directoryClarity: OnboardingFlags['directoryClarityScore'] =
    raw?.directoryClarityScore === 'low' || raw?.directoryClarityScore === 'moderate' || raw?.directoryClarityScore === 'excellent'
      ? raw.directoryClarityScore
      : def.directoryClarityScore;

  return {
    showOnboardingOverlay: typeof raw.showOnboardingOverlay === 'boolean' ? raw.showOnboardingOverlay : def.showOnboardingOverlay,
    badges: { jsExtensionCompliance: compliance },
    tips: Array.isArray(raw.tips) ? raw.tips.filter((t: any) => typeof t === 'string') : def.tips,
    themeTokenAudit: raw?.themeTokenAudit === 'complete' ? 'complete' : 'pending',
    directoryClarityScore: directoryClarity,
    badgePulse: raw?.badgePulse === true || compliance === 100,
  };
}
