// project-root/src/components/ContributorRhythm.tsx

import React from 'react';

interface RhythmPulse {
  contributor: string;
  timestamp: number;
  intensity: number; // 0–1 scale
}

interface ContributorRhythmProps {
  pulses: RhythmPulse[];
}

export const ContributorRhythm: React.FC<ContributorRhythmProps> = ({ pulses }) => {
  return (
    <div style={{ display: 'flex', gap: '4px', alignItems: 'center' }}>
      {pulses.map((pulse, idx) => (
        <div
          key={idx}
          title={`${pulse.contributor} @ ${new Date(pulse.timestamp).toLocaleString()}`}
          style={{
            width: '10px',
            height: '30px',
            backgroundColor: `rgba(255, 0, 150, ${pulse.intensity})`,
            borderRadius: '2px',
            transition: 'height 0.3s ease',
          }}
        />
      ))}
    </div>
  );
};
