// project-root/src/components/visualization/AuditDensityOverlay.tsx

import React, { useState } from 'react';
import type { AuditEntry } from '../../types/audit/auditTypes';

interface Props {
  entries: AuditEntry[];
}

export const AuditDensityOverlay: React.FC<Props> = ({ entries }) => {
  const [hovered, setHovered] = useState<{
    contributor: string;
    timestamp: number;
    x: number;
    y: number;
  } | null>(null);

  const buckets = new Map<number, { count: number; contributor: string; timestamp: number }>();
  const bucketSize = 60 * 60 * 1000; // 1 hour

  entries.forEach((entry) => {
    const bucket = Math.floor(entry.timestamp / bucketSize) * bucketSize;
    const existing = buckets.get(bucket);
    buckets.set(bucket, {
      count: (existing?.count ?? 0) + 1,
      contributor: entry.contributor,
      timestamp: entry.timestamp,
    });
  });

  const maxCount = Math.max(...[...buckets.values()].map((b) => b.count));

  return (
    <svg width="100%" height="100">
      {[...buckets.entries()].map(([time, data], idx) => {
        const x = idx * 10;
        const height = (data.count / maxCount) * 100;
        const y = 100 - height;

        return (
          <g key={idx}>
            <rect
              x={x}
              y={y}
              width={8}
              height={height}
              fill="#4A90E2"
              onMouseEnter={() =>
                setHovered({
                  contributor: data.contributor,
                  timestamp: data.timestamp,
                  x,
                  y,
                })
              }
              onMouseLeave={() => setHovered(null)}
            />
            {hovered && hovered.x === x && (
              <foreignObject x={x + 10} y={y - 40} width={160} height={50}>
                <div
                  style={{
                    background: '#fff',
                    border: '1px solid #ccc',
                    padding: '6px',
                    fontSize: '12px',
                    borderRadius: '4px',
                  }}
                >
                  <div><strong>{hovered.contributor}</strong></div>
                  <div>{new Date(hovered.timestamp).toLocaleString()}</div>
                </div>
              </foreignObject>
            )}
          </g>
        );
      })}
    </svg>
  );
};
