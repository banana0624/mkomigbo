// project-root/src/models/AuditEntry.ts

// project-root/src/models/AuditEntry.ts

// project-root/src/models/AuditEntry.ts

export type AuditAction =
  | 'dry_run'
  | 'commit'
  | 'tooltip_step'
  | 'onboarding_start'
  | 'onboarding_complete';

export interface AuditEntry {
  id: string;
  contributor: string; // ✅ Required for AuditHeatmap
  contributorId: string;
  timestamp: number;
  action: AuditAction;
  metadata?: Record<string, any>;
}
