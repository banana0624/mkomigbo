// project-root/src/components/audit/AuditViewer.tsx

import React, { useState } from 'react';
import { DryRunToggle } from '../filters/DryRunToggle';
import { AuditDensityOverlay } from '../visualization/AuditDensityOverlay';
import { StateHopPath } from '../visualization/StateHopPath';
import { UnknownStatePulse } from '../visualization/UnknownStatePulse';
import type { AuditEntry } from '../../types/audit/auditTypes';

export const AuditViewer: React.FC<{ entries: AuditEntry[] }> = ({ entries }) => {
  const [dryRunOnly, setDryRunOnly] = useState(false);
  const [showDensity, setShowDensity] = useState(true);

  const filters = { contributor: null, stage: null, startDate: null, endDate: null, dryRunOnly };
  const filtered = entries.filter((entry) => {
    if (dryRunOnly && !entry.dryRun) return false;
    return true;
  });

  return (
    <div>
      <div style={{ display: 'flex', gap: '1rem', alignItems: 'center' }}>
        <DryRunToggle dryRunOnly={dryRunOnly} setDryRunOnly={setDryRunOnly} />
        <label>
          <input
            type="checkbox"
            checked={showDensity}
            onChange={(e) => setShowDensity(e.target.checked)}
          />
          Show Audit Density
        </label>
      </div>

      {showDensity && <AuditDensityOverlay entries={filtered} />}

      <svg width="100%" height="400">
        {filtered.map((entry, idx) => {
          const fromX = idx * 100;
          const toX = fromX + 80;
          const y = 200;

          return entry.to
            ? <StateHopPath key={idx} fromX={fromX} fromY={y} toX={toX} toY={y} label={entry.to} />
            : <UnknownStatePulse key={idx} x={fromX} y={y} />;
        })}
      </svg>
    </div>
  );
};
