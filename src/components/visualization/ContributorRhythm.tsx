// project-root/src/components/visualization/ContributorRhythm.tsx

import React from 'react';

interface RhythmProps {
  streakCount: number;
  dryRunRatio: number;
}

export const ContributorRhythm: React.FC<RhythmProps> = ({ streakCount, dryRunRatio }) => {
  const pulseColor = dryRunRatio > 0.8 ? '#00cc99' : '#ffaa00';
  const pulseSize = Math.min(100, streakCount * 10);

  return (
    <div style={{ textAlign: 'center', padding: '24px' }}>
      <svg width="220" height="120">
        <circle
          cx="110"
          cy="60"
          r={pulseSize / 2}
          fill={pulseColor}
          stroke="#333"
          strokeWidth="2"
        >
          <animate
            attributeName="r"
            values={`${pulseSize / 2};${pulseSize};${pulseSize / 2}`}
            dur="1.5s"
            repeatCount="indefinite"
          />
        </circle>
        <text
          x="110"
          y="60"
          textAnchor="middle"
          dominantBaseline="middle"
          fontSize="16"
          fill="#333"
        >
          {streakCount}🔥
        </text>
      </svg>
      <div style={{ marginTop: '12px', fontSize: '0.9rem', color: '#666' }}>
        Dry-run mastery: {(dryRunRatio * 100).toFixed(1)}%
      </div>
    </div>
  );
};
