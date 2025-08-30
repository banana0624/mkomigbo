// project-root/src/components/summary/ContributorStreaks.tsx

import React from 'react';
import type { AuditEntry } from '../../types/audit/auditTypes';

interface Props {
  entries: AuditEntry[];
}

export const ContributorStreaks: React.FC<Props> = ({ entries }) => {
  const streaks = entries.reduce<Record<string, Set<string>>>((acc, entry) => {
    const date = new Date(entry.timestamp).toISOString().split('T')[0];
    const name = entry.contributor;
    if (!acc[name]) acc[name] = new Set();
    acc[name].add(date);
    return acc;
  }, {});

  return (
    <div style={{ marginTop: '2rem' }}>
      <h3>Contributor Streaks</h3>
      {Object.entries(streaks).map(([name, dates]) => (
        <div key={name} style={{ marginBottom: '1rem' }}>
          <strong>{name}</strong>: {dates.size} active days
          <svg width="100" height="10" style={{ marginTop: '0.5rem', display: 'block' }}>
            {[...dates].map((date, i) => (
              <circle
                key={date}
                cx={i * 12}
                cy={5}
                r={4}
                fill="#4A90E2"
              >
                <animate
                  attributeName="r"
                  values="4;6;4"
                  dur="1.5s"
                  repeatCount="indefinite"
                  begin={`${i * 0.2}s`}
                />
              </circle>
            ))}
          </svg>
        </div>
      ))}
    </div>
  );
};
