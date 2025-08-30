// project-root/src/components/AuditHeatmap/AuditHeatmap.tsx

import React from 'react';
import theme from '../../styles/theme.module.css';

type HeatmapCell = {
  date: string;
  intensity: number; // 0 (none) to 5 (high)
  lifecycleStage: 'onboarding' | 'active' | 'paused' | 'completed';
};

type Props = {
  data: HeatmapCell[];
};

export const AuditHeatmap: React.FC<Props> = ({ data }) => {
  return (
    <div className={`${theme.pMd} ${theme.border}`}>
      <h3 className={theme.textInfo}>Audit Heatmap</h3>
      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(7, 1fr)', gap: '4px' }}>
        {data.map((cell, index) => (
          <div
            key={index}
            title={`${cell.date} — ${cell.lifecycleStage}`}
            style={{
              backgroundColor: getColor(cell.intensity, cell.lifecycleStage),
              width: '100%',
              aspectRatio: '1 / 1',
              borderRadius: '4px',
              transition: 'background-color var(--transition-medium)'
            }}
          />
        ))}
      </div>
    </div>
  );
};

function getColor(intensity: number, stage: string): string {
  const baseColors: Record<string, string> = {
    onboarding: '#3498db',
    active: '#f39c12',
    paused: '#95a5a6',
    completed: '#2ecc71'
  };
  const opacity = 0.2 + intensity * 0.15;
  return `${baseColors[stage]}${Math.round(opacity * 255).toString(16).padStart(2, '0')}`;
}
