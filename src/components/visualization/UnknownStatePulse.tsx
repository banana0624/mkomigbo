// project-root/src/components/visualization/UnknownStatePulse.tsx

import React from 'react';

export const UnknownStatePulse: React.FC<{ x: number; y: number }> = ({ x, y }) => (
  <circle cx={x} cy={y} r={10} stroke="orange" strokeWidth={2} fill="none">
    <animate attributeName="r" values="10;15;10" dur="1s" repeatCount="indefinite" />
    <animate attributeName="stroke-opacity" values="1;0.5;1" dur="1s" repeatCount="indefinite" />
  </circle>
);