// project-root/src/components/DashboardSidebar.tsx

import React from 'react';
import { useAuditStats } from '../hooks/useAuditStats';
import { DensityChart } from '../components/visualization/DensityChart';
import { RatioMeter } from '../components/visualization/RatioMeter';
import { LifecycleStageList } from '../components/visualization/LifecycleStageList';
import { ContributorBadge } from '../components/visualization/ContributorBadge';

export const DashboardSidebar: React.FC = () => {
  const { contributorDensity, dryRunRatio, lifecycleCounts } = useAuditStats();

  return (
    <aside className="sidebar">
      <h3>Audit Summary</h3>

      <div style={{ marginBottom: '1rem' }}>
        {Object.entries(contributorDensity).map(([name, count]) => (
          <ContributorBadge key={name} name={name} count={count} />
        ))}
      </div>

      <DensityChart data={contributorDensity} />
      <RatioMeter label="Dry-run Ratio" value={dryRunRatio} />
      <LifecycleStageList stages={lifecycleCounts} />
    </aside>
  );
};
