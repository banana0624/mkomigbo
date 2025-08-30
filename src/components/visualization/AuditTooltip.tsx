// project-root/src/components/visualization/AuditTooltip.tsx

import React from 'react';

interface Props {
  x: number;
  y: number;
  contributor: string;
  timestamp: number;
  dryRun: boolean;
}

export const AuditTooltip: React.FC<Props> = ({ x, y, contributor, timestamp, dryRun }) => (
  <foreignObject x={x} y={y - 40} width={150} height={40}>
    <div style={{ background: '#fff', border: '1px solid #ccc', padding: '4px', fontSize: '12px' }}>
      <div><strong>{contributor}</strong></div>
      <div>{new Date(timestamp).toLocaleString()}</div>
      <div>{dryRun ? 'Dry Run' : 'Real'}</div>
    </div>
  </foreignObject>
);