// project-root/src/components/visualization/DensityChart.tsx

import React from 'react';

export const DensityChart: React.FC<{ data: Record<string, number> }> = ({ data }) => {
  const max = Math.max(...Object.values(data));

  return (
    <div style={{ display: 'flex', gap: '0.5rem' }}>
      {Object.entries(data).map(([name, count]) => {
        const intensity = count / max;
        const pulse = intensity > 0.8;

        return (
          <div
            key={name}
            style={{
              width: '40px',
              height: `${intensity * 100}px`,
              background: pulse ? 'linear-gradient(to top, #4A90E2, #50E3C2)' : '#4A90E2',
              animation: pulse ? 'pulse 1s infinite' : undefined,
            }}
            title={`${name}: ${count}`}
          />
        );
      })}
      <style>
        {`
          @keyframes pulse {
            0% { transform: scaleY(1); }
            50% { transform: scaleY(1.1); }
            100% { transform: scaleY(1); }
          }
        `}
      </style>
    </div>
  );
};

