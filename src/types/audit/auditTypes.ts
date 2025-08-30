// project-root/src/types/audit/auditTypes.ts

export interface AuditEntry {
  id: string; // ✅ Add this line
  contributor: string;
  timestamp: number;
  from?: string;
  to?: string;
  dryRun?: boolean;
  // ...other fields
}