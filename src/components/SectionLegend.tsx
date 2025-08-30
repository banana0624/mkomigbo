// project-root/src/components/SectionLegend.tsx

import React from 'react';

interface LegendItem {
  label: string;
  color: string;
}

interface SectionLegendProps {
  items: LegendItem[];
}

export const SectionLegend: React.FC<SectionLegendProps> = ({ items }) => (
  <div style={{ display: 'flex', gap: '12px', marginTop: '8px' }}>
    {items.map((item, idx) => (
      <div key={idx} style={{ display: 'flex', alignItems: 'center', gap: '4px' }}>
        <div style={{ width: '12px', height: '12px', backgroundColor: item.color }} />
        <span>{item.label}</span>
      </div>
    ))}
  </div>
);
