// project-root/src/components/visualization/ContributorBadge.tsx

import React from 'react';

export const ContributorBadge: React.FC<{ name: string; count: number }> = ({ name, count }) => {
  const level = count > 20 ? 'Lead' : count > 10 ? 'Active' : 'New';

  return (
    <div style={{ border: '1px solid #ccc', padding: '4px 8px', borderRadius: '4px' }}>
      <strong>{name}</strong> <span style={{ color: '#888' }}>({level})</span>
    </div>
  );
};
