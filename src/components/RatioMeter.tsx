// project-root/src/components/RatioMeter.tsx

import React from 'react';

interface RatioMeterProps {
  label: string;
  numerator: number;
  denominator: number;
}

export const RatioMeter: React.FC<RatioMeterProps> = ({ label, numerator, denominator }) => {
  const ratio = denominator > 0 ? numerator / denominator : 0;

  return (
    <div style={{ padding: '8px' }}>
      <strong>{label}</strong>
      <div style={{ background: '#eee', borderRadius: '4px', overflow: 'hidden', height: '12px' }}>
        <div
          style={{
            width: `${Math.min(100, ratio * 100)}%`,
            background: '#007bff',
            height: '100%',
          }}
        />
      </div>
      <small>{numerator} / {denominator}</small>
    </div>
  );
};
