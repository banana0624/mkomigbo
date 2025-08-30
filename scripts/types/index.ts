// project-root/scripts/tpyes/index.ts

// existing exports remain
export type ValidationStatus = 'valid' | 'invalid' | 'pending';

export interface AuditEvent {
  timestamp: number;
  field?: string;
  status?: ValidationStatus;
  rhythmIndex?: number;
}

// ——— Onboarding-specific types ———
export type OnboardingEventType =
  | 'overlay_shown'
  | 'overlay_hidden'
  | 'overlay_step'
  | 'badge_awarded'
  | 'overlay_shown'
  | 'overlay_hidden'
  | 'overlay_step'
  | 'badge_awarded'
  | 'field_valid'
  | 'field_invalid';


export interface OnboardingEvent extends AuditEvent {
  type: OnboardingEventType;
  step?: number;
  milestone?: string;
}
