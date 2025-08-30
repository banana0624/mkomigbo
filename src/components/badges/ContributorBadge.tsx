// project-root/src/components/badges/ContributorBadge.tsx

import React from 'react';

type BadgeType = 'streak' | 'dryRunMaster';
type BadgeLevel = 'gold' | 'silver' | 'bronze';

interface Props {
  type: BadgeType;
  level?: BadgeLevel;
}

export const ContributorBadge: React.FC<Props> = ({ type, level }) => {
  const label = type === 'streak' ? `Streak (${level})` : 'Dry Run Master';
  return (
    <div style={{
      backgroundColor: '#f0f0f0',
      padding: '6px 12px',
      borderRadius: '8px',
      fontWeight: 'bold',
      fontSize: '0.85rem',
      color: '#333',
      marginTop: '8px',
      display: 'inline-block',
    }}>
      {label}
    </div>
  );
};
