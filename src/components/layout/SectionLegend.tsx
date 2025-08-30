// project-root/src/components/layout/SectionLegend.tsx

import React from 'react';

interface Props {
  contributorId: string;
  onSelectContributor: (id: string) => void;
}

export const SectionLegend: React.FC<Props> = ({ contributorId, onSelectContributor }) => {
  return (
    <div style={{ marginBottom: '16px', fontSize: '0.9rem', color: '#555' }}>
      <strong>Viewing:</strong> {contributorId}
      <button
        style={{ marginLeft: '12px' }}
        onClick={() => onSelectContributor('nextContributor')}
      >
        Switch Contributor
      </button>
    </div>
  );
};
