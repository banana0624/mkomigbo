// project-root/dashboard/ui/BadgePulse.tsx

import React from 'react';
import './BadgePulse.css';

export function BadgePulse({ label = 'Milestone Reached' }: { label?: string }) {
  return (
    <div className="badge-pulse">
      <div className="pulse-ring" />
      <div className="pulse-core">{label}</div>
    </div>
  );
}
