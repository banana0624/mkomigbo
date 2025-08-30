// project-root/src/components/summary/ContributorSummary.tsx

import React from 'react';
import type { AuditEntry } from '../../types/audit/auditTypes';
import { ContributorBadge } from '../visualization/ContributorBadge';

interface Props {
  entries: AuditEntry[];
}

export const ContributorSummary: React.FC<Props> = ({ entries }) => {
  const summary = entries.reduce<Record<string, { total: number; dryRun: number; transitions: Record<string, number> }>>(
    (acc, entry) => {
      const name = entry.contributor;
      if (!acc[name]) {
        acc[name] = { total: 0, dryRun: 0, transitions: {} };
      }
      acc[name].total += 1;
      if (entry.dryRun) acc[name].dryRun += 1;
      const key = `${entry.from}→${entry.to}`;
      acc[name].transitions[key] = (acc[name].transitions[key] || 0) + 1;
      return acc;
    },
    {}
  );

  {Object.entries(summary).map(([name, data]) => (
  <div key={name} style={{ marginBottom: '1rem', padding: '1rem', border: '1px solid #ccc' }}>
    <ContributorBadge name={name} count={data.total} />
    <p>Dry Runs: {data.dryRun}</p>
    <p>Transitions:</p>
    <ul>
      {Object.entries(data.transitions).map(([transition, count]) => (
        <li key={transition}>{transition}: {count}</li>
      ))}
    </ul>
  </div>
))}

  return (
    <div style={{ marginTop: '2rem' }}>
      <h3>Contributor Summary</h3>
      {Object.entries(summary).map(([name, data]) => (
        <div key={name} style={{ marginBottom: '1rem', padding: '1rem', border: '1px solid #ccc' }}>
          <strong>{name}</strong>
          <p>Total Entries: {data.total}</p>
          <p>Dry Runs: {data.dryRun}</p>
          <p>Transitions:</p>
          <ul>
            {Object.entries(data.transitions).map(([transition, count]) => (
              <li key={transition}>{transition}: {count}</li>
            ))}
          </ul>
        </div>
      ))}
    </div>
  );
};
