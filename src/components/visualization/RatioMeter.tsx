// project-root/src/components/visualization/RatioMeter.tsx

import React from 'react';

export const RatioMeter: React.FC<{ label: string; value: number }> = ({ label, value }) => (
  <div>
    <strong>{label}</strong>: {(value * 100).toFixed(1)}%
  </div>
);
