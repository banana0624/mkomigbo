// project-root/src/AppOverlayExample.tsx

import React from 'react';
import { OnboardingOverlay } from './ui/onboarding/OnboardingOverlay.js';
import { useOnboardingFlags } from './ui/onboarding/useOnboardingFlags.js';
import { Badge } from './ui/badges/Badge.js';
import { LaunchOverlay } from './ui/onboarding/LaunchOverlay.js';

export function AppOverlayExample() {
  const flags = useOnboardingFlags();
  const showLaunch = (window as any).__LAUNCH_OVERLAY__ === true;

  return (
    <>
      {showLaunch && (
        <LaunchOverlay
          show={true}
          links={[
            { title: 'Launch Post', href: './docs/launch-post.md' },
            { title: 'Contributor Guide', href: './CONTRIBUTING.md' },
          ]}
        />
      )}

      <div style={{ padding: 16 }}>
        <Badge label=".js extension compliance" value={flags.badges.jsExtensionCompliance} />
      </div>

      <OnboardingOverlay
        flags={flags}
        links={[
          { title: 'Launch Post', href: './docs/launch-post.md' },
          { title: 'README', href: './README.md' },
        ]}
      />
    </>
  );
}

