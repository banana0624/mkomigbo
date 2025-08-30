// project-root/src/components/onboarding/OnboardingOverlay.tsx

// project-root/src/components/onboarding/OnboardingOverlay.tsx

import React, { useState, useEffect } from 'react';
import { ContributorBadge } from '../badges/ContributorBadge';
import { logAuditEvent } from '../../utils/onboardingAudit';

export const OnboardingOverlay: React.FC = () => {
  const [step, setStep] = useState(0);
  const steps = [
    {
      targetId: 'filters',
      message: 'Use these filters to narrow down audit entries by contributor, stage, or date.',
      badge: <ContributorBadge type="streak" level="gold" />,
    },
    {
      targetId: 'viewer',
      message: 'This viewer shows the filtered audit trail with full traceability.',
      badge: <ContributorBadge type="dryRunMaster" />,
    },
    {
      targetId: 'timeline',
      message: 'Visualize lifecycle transitions over time here.',
    },
    {
      targetId: 'sidebar',
      message: 'The sidebar summarizes contributor activity and dry-run ratios.',
    },
  ];

  useEffect(() => {
    const timestamp = Date.now();
    const contributor = 'currentUser';

    if (step === 0) {
      logAuditEvent({ type: 'onboarding_started', contributor, timestamp });
    } else if (step < steps.length) {
      logAuditEvent({ type: 'tooltip_step_completed', contributor, step, timestamp });
    } else {
      logAuditEvent({ type: 'onboarding_completed', contributor, timestamp });
    }
  }, [step]);

  if (step >= steps.length) return null;

  const { targetId, message, badge } = steps[step];
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
        backgroundColor: 'rgba(255, 255, 255, 0.95)',
        border: '2px solid #0077cc',
        borderRadius: '12px',
        padding: '16px',
        boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
        zIndex: 1000,
      }}
    >
      <div style={{ fontSize: '1rem', color: '#333' }}>
        {message}
        {badge && (
          <div style={{
            marginTop: '8px',
            backgroundColor: '#f0f0f0',
            padding: '6px 12px',
            borderRadius: '8px',
            fontWeight: 'bold',
            fontSize: '0.85rem',
            color: '#333',
            display: 'inline-block',
          }}>
            {badge}
          </div>
        )}
        <svg style={{ marginTop: '12px', width: '100%', height: '40px' }}>
          <path d="M0,0 C50,100 150,100 200,0" stroke="blue" fill="none" />
        </svg>
        <div style={{ marginTop: '16px', display: 'flex', gap: '12px' }}>
          <button onClick={() => setStep(step + 1)}>Next</button>
          <button onClick={() => setStep(steps.length)}>Dismiss</button>
        </div>
      </div>
    </div>
  ) : null;
};
