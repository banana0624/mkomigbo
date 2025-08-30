// project-root/src/utils/onboardingAudit.ts

export type AuditEventType = 'onboarding_started' | 'tooltip_step_completed' | 'onboarding_completed';

interface AuditEvent {
  type: AuditEventType;
  contributor: string;
  step?: number;
  timestamp: number;
}

export function logAuditEvent(event: AuditEvent) {
  console.log('Audit Event:', event);
  // Future: send to backend or store locally
}

