// project-root/src/components/AuditOverlay/AuditOverlay.tsx

import React from 'react';
import theme from '../../styles/theme.module.css';

type AuditEvent = {
  timestamp: string;
  action: string;
  contributorId: string;
  lifecycleStage: 'onboarding' | 'active' | 'paused' | 'completed';
};

type Props = {
  events: AuditEvent[];
};

export const AuditOverlay: React.FC<Props> = ({ events }) => {
  return (
    <div className={`${theme.bgSecondary} ${theme.pMd} ${theme.border}`}>
      <h3 className={theme.textInfo}>Audit Summary</h3>
      <ul className={theme.pSm}>
        {events.map((event, index) => (
          <li
            key={index}
            className={`${theme.textMuted} ${theme.mSm}`}
            style={{
              animation: `fadeIn 0.3s ease-in ${index * 100}ms`,
              animationFillMode: 'both'
            }}
          >
            <strong>{event.contributorId}</strong> — {event.action} at {event.timestamp}
            <span className={theme.textSuccess}> ({event.lifecycleStage})</span>
          </li>
        ))}
      </ul>
    </div>
  );
};
