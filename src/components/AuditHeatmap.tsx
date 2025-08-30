// project-root/src/components/AuditHeatmap.tsx

import React from 'react';

export interface AuditEntry {
  id: string;
  contributor: string;
  timestamp: number;
  action: string;
}

interface AuditHeatmapProps {
  entries: AuditEntry[];
}

export const AuditHeatmap: React.FC<AuditHeatmapProps> = ({ entries }) => {
  if (!entries || entries.length === 0) {
    return <div>No audit data available.</div>;
  }

  const groupedByDay = entries.reduce((acc, entry) => {
    const day = new Date(entry.timestamp).toISOString().split('T')[0];
    acc[day] = acc[day] ? acc[day] + 1 : 1;
    return acc;
  }, {} as Record<string, number>);

  return (
    <div style={{ padding: '16px' }}>
      <h3>Audit Density Heatmap</h3>
      <div style={{ display: 'flex', gap: '6px', flexWrap: 'wrap' }}>
        {Object.entries(groupedByDay).map(([day, count]) => (
          <div
            key={day}
            title={`${day}: ${count} entries`}
            style={{
              width: '20px',
              height: '20px',
              backgroundColor: `rgba(0, 123, 255, ${Math.min(1, count / 10)})`,
              borderRadius: '4px',
            }}
          />
        ))}
      </div>
    </div>
  );
};
