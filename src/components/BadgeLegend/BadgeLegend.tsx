// project-root/src/components/BadgeLegend/BadgeLegend.tsx

import React from 'react';
import theme from '../../styles/theme.module.css';

type BadgeState = 'newcomer' | 'momentum' | 'veteran';

const badgeDescriptions: Record<BadgeState, string> = {
  newcomer: 'New contributor onboarding into the platform.',
  momentum: 'Actively contributing with growing rhythm.',
  veteran: 'Long-term contributor with sustained impact.'
};

export const BadgeLegend: React.FC = () => {
  return (
    <div className={`${theme.bgLightContainer} ${theme.pMd} ${theme.border}`}>
      <h3 className={theme.textInfo}>Badge Legend</h3>
      <ul className={theme.pSm}>
        {Object.entries(badgeDescriptions).map(([badge, description]) => (
          <li key={badge} className={`${theme.mSm} ${theme.textMuted}`}>
            <strong className={theme.textSuccess}>{badge}</strong>: {description}
          </li>
        ))}
      </ul>
    </div>
  );
};
