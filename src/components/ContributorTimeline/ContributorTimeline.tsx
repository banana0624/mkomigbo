// project-root/src/components/ContributorTimeline/ContributorTimeline.tsx

import React from 'react';
import theme from '../../styles/theme.module.css';

type TimelineEntry = {
  label: string;
  timestamp: string;
  badge?: string;
};

type Props = {
  entries: TimelineEntry[];
};

export const ContributorTimeline: React.FC<Props> = ({ entries }) => {
  return (
    <div className={`${theme.pMd} ${theme.transition}`}>
      <h3 className={theme.textInfo}>Contributor Timeline</h3>
      <ul className={theme.pSm}>
        {entries.map((entry, index) => (
          <li
            key={index}
            className={`${theme.textMuted} ${theme.mSm}`}
            style={{
              animation: `fadeInUp 0.4s ease-out ${index * 100}ms`,
              animationFillMode: 'both'
            }}
          >
            <strong>{entry.label}</strong> — {entry.timestamp}
            {entry.badge && <span className={theme.textSuccess}> ({entry.badge})</span>}
          </li>
        ))}
      </ul>
    </div>
  );
};
