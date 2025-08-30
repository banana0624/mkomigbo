// project-root/src/components/filters/FilterSummaryChip.tsx

import React from 'react';

interface Props {
  contributor: string | null;
  stage: string | null;
  startDate: string | null;
  endDate: string | null;
  dryRunOnly: boolean;
  onReset: () => void;
}

export const FilterSummaryChip: React.FC<Props> = ({
  contributor,
  stage,
  startDate,
  endDate,
  dryRunOnly,
  onReset,
}) => {
  const activeFilters = [
    contributor && `Contributor: ${contributor}`,
    stage && `Stage: ${stage}`,
    startDate && `Start: ${startDate}`,
    endDate && `End: ${endDate}`,
    dryRunOnly && `Dry Runs Only`,
  ].filter(Boolean);

  return (
    <div style={{ margin: '1rem 0', display: 'flex', alignItems: 'center', gap: '1rem' }}>
      <div style={{ fontWeight: 'bold' }}>
        Active Filters: {activeFilters.length > 0 ? activeFilters.join(', ') : 'None'}
      </div>
      <button onClick={onReset}>Reset Filters</button>
    </div>
  );
};
