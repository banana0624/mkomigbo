// project-root/src/components/visualization/LifecycleStageList.tsx

import React from 'react';

export const LifecycleStageList: React.FC<{ stages: Record<string, number> }> = ({ stages }) => (
  <ul>
    {Object.entries(stages).map(([stage, count]) => (
      <li key={stage}>{stage}: {count}</li>
    ))}
  </ul>
);
