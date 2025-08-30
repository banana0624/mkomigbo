// project-root/scripts/utils/metrics.ts

import { traceOnboardingEvent, rhythmTick } from '../onboarding/auditHeatmap';

export const emitMetric = (
  type: 'field_valid' | 'field_invalid' | 'badge_awarded' | 'overlay_step',
  field?: string,
  status?: 'valid' | 'invalid'
): void => {
  traceOnboardingEvent({
    type,
    field,
    status,
    timestamp: Date.now(),
    rhythmIndex: rhythmTick(),
  } as any);
};

/** Metrics collection for momentum and onboarding clarity. */
export function recordMetric(name: string, value: number, tags?: Record<string,string>) { /* TODO */ }

