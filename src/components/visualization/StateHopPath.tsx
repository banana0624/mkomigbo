// project-root/src/components/visualization/StateHopPath.tsx

import React from 'react';

interface StateHopPathProps {
  fromX: number;
  fromY: number;
  toX: number;
  toY: number;
  label?: string;
}

export const StateHopPath: React.FC<StateHopPathProps> = ({ fromX, fromY, toX, toY, label }) => {
  const path = `M${fromX},${fromY} C${fromX + 50},${fromY} ${toX - 50},${toY} ${toX},${toY}`;

  return (
    <g>
      <path d={path} stroke="#4A90E2" fill="none" strokeWidth={2}>
        <animate attributeName="stroke-dashoffset" from="1000" to="0" dur="1s" fill="freeze" />
      </path>
      {label && <text x={(fromX + toX) / 2} y={(fromY + toY) / 2 - 10} textAnchor="middle">{label}</text>}
    </g>
  );
};