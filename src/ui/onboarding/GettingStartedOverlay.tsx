// project-root/src/ui/onboarding/GettingStartedOverlay.tsx

import React from 'react';
import './Overlay.css';

export function GettingStartedOverlay() {
  return (
    <div className="overlay-root" role="dialog" aria-modal="true">
      <div className="overlay-card">
        <h2>🎉 Welcome, Contributor</h2>
        <p>This project celebrates clarity, rhythm, and your momentum.</p>
        <ul>
          <li><a href="./docs/launch-post.md">📣 Launch Post</a></li>
          <li><a href="./CONTRIBUTING.md">🧭 Contributor Guide</a></li>
          <li><a href="./.copilot/audit/report.json">📊 Audit Report</a></li>
        </ul>
        <p>Let’s keep the rhythm alive.</p>
      </div>
    </div>
  );
}
