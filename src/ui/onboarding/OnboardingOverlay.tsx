// project-root/src/ui/onboarding/OnboardingOverlay.tsx

import React from 'react';
import './Overlay.css';

/**
 * Contributor-safe flags used to drive onboarding overlays and badge animations.
 */
export type OnboardingFlags = {
  showOnboardingOverlay: boolean;
  badgePulse?: boolean;
  badges: {
    jsExtensionCompliance: number;
  };
  tips: string[];
  themeTokenAudit?: 'pending' | 'complete';
  directoryClarityScore?: 'low' | 'moderate' | 'excellent';
};

type Props = {
  flags: OnboardingFlags;
  links?: { title: string; href: string }[];
};

export function OnboardingOverlay({ flags, links = [] }: Props) {
  if (!flags.showOnboardingOverlay) return null;
  return (
    <div className="overlay-root" role="dialog" aria-modal="true">
      <div className="overlay-card">
        <h2>Welcome to the Rhythm</h2>
        <p>We celebrate clarity. Let’s nudge imports toward explicit .js extensions.</p>
        <ul>
          {flags.tips.map((t, i) => <li key={i}>{t}</li>)}
        </ul>
        <div className="overlay-links">
          {links.map(l => (
            <a key={l.href} href={l.href} target="_blank" rel="noreferrer">{l.title}</a>
          ))}
        </div>
      </div>
    </div>
  );
}
