// project-root/src/components/visualization/DryRunDensityChart.tsx

import React from 'react';

interface Props {
  entries: { dryRun: boolean }[];
}

export const DryRunDensityChart: React.FC<Props> = ({ entries }) => {
  const dryRunCount = entries.filter((e) => e.dryRun).length;
  const realRunCount = entries.length - dryRunCount;
  const max = Math.max(dryRunCount, realRunCount);

  return (
    <div style={{ display: 'flex', gap: '1rem', marginTop: '1rem' }}>
      <div style={{ textAlign: 'center' }}>
        <div
          style={{
            height: `${(dryRunCount / max) * 100}px`,
            width: '40px',
            background: '#F5A623',
          }}
        />
        <div>Dry Runs ({dryRunCount})</div>
      </div>
      <div style={{ textAlign: 'center' }}>
        <div
          style={{
            height: `${(realRunCount / max) * 100}px`,
            width: '40px',
            background: '#4A90E2',
          }}
        />
        <div>Real Runs ({realRunCount})</div>
      </div>
    </div>
  );
};
