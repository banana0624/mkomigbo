// project-root/src/components/summary/FilteredCountIndicator.tsx

import React from 'react';

interface Props {
  count: number;
}

export const FilteredCountIndicator: React.FC<Props> = ({ count }) => (
  <div style={{ margin: '1rem 0', fontWeight: 'bold' }}>
    Showing {count} matching audit entries
  </div>
);

