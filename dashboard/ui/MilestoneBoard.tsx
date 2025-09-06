// project-root/dashboard/ui/MilestoneBoard.tsx

import React from 'react';
import { BadgePulse } from './BadgePulse';

const milestones = [
  'Initialized Project',
  'Scaffolded CLI',
  'Restored Config Integrity',
  'Activated Trace Pipeline',
  'Celebrated First Commit'
];

export function MilestoneBoard() {
  return (
    <div style={{ display: 'grid', gap: '1rem', padding: '2rem' }}>
      {milestones.map((label, index) => (
        <BadgePulse key={index} label={label} />
      ))}
    </div>
  );
}
