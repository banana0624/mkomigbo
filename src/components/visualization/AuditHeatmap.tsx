// project-root/src/components/visualization/AuditHeatmap.tsx

import React from 'react';
import type { AuditEntry } from '../../types/audit/auditTypes';

export const AuditHeatmap: React.FC<{ entries: AuditEntry[] }> = ({ entries }) => {
  const buckets = new Array(24).fill(0);
  entries.forEach((entry) => {
    const hour = new Date(entry.timestamp).getHours();
    buckets[hour]++;
  });

  const max = Math.max(...buckets);

  return (
    <div style={{ marginTop: '2rem' }}>
      <h3>Hourly Audit Heatmap</h3>
      <div style={{ display: 'flex', gap: '4px' }}>
        {buckets.map((count, hour) => (
          <div
            key={hour}
            title={`${hour}:00 → ${count} entries`}
            style={{
              width: '20px',
              height: '60px',
              background: `rgba(74, 144, 226, ${count / max})`,
              borderRadius: '4px',
            }}
          />
        ))}
      </div>
    </div>
  );
};
