// project-root/src/models/OnboardEvent.ts

export interface OnboardEvent {
  id: string;
  contributorId: string;
  step: number;
  timestamp: number;
  type: 'started' | 'step_completed' | 'completed';
}
