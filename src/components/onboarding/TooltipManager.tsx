// project-root/src/components/onboarding/TooltipManager.tsx

import React, { useEffect, useState } from 'react';

interface TooltipStep {
  targetId: string;
  message: string;
  badge?: React.ReactNode;
}

interface TooltipManagerProps {
  steps: TooltipStep[];
  onComplete?: () => void;
}

export const TooltipManager: React.FC<TooltipManagerProps> = ({ steps, onComplete }) => {
  const [stepIndex, setStepIndex] = useState(0);

  useEffect(() => {
    if (stepIndex >= steps.length && onComplete) {
      onComplete();
    }
  }, [stepIndex, steps.length, onComplete]);

  if (stepIndex >= steps.length) return null;

  const { targetId, message, badge } = steps[stepIndex];
  const target = document.getElementById(targetId);
  const rect = target?.getBoundingClientRect();

  return target ? (
    <div
      style={{
        position: 'absolute',
        top: rect.top + window.scrollY,
        left: rect.left + window.scrollX,
        width: rect.width,
        height: rect.height,
        backgroundColor: 'rgba(255,255,255,0.95)',
        border: '2px solid #0077cc',
        borderRadius: '12px',
        padding: '16px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.1)',
        zIndex: 1000,
      }}
    >
      <div style={{ fontSize: '1rem', color: '#333' }}>
        {message}
        {badge && <div style={{ marginTop: '8px' }}>{badge}</div>}
        <div style={{ marginTop: '16px', display: 'flex', gap: '12px' }}>
          <button onClick={() => setStepIndex(stepIndex + 1)}>Next</button>
          <button onClick={() => setStepIndex(steps.length)}>Dismiss</button>
        </div>
      </div>
    </div>
  ) : null;
};
