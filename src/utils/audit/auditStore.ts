// project-root/src/utils/audit/auditStore.ts

import { v4 as uuidv4 } from 'uuid';
import type { AuditEntry } from '../../types/audit/auditTypes';

// Simulated raw audit data (replace with actual fetch logic if available)
const auditLog: Partial<AuditEntry>[] = [
  {
    id: 'e1', // ✅ Use 'id' instead of 'entryId'
    from: 'pending',
    to: 'validated',
    timestamp: Date.now() - 100000,
    dryRun: true,
    contributor: 'Theo',
  },
  {
    id: 'e2',
    from: 'validated',
    to: 'backedUp',
    timestamp: Date.now() - 50000,
    dryRun: false,
    contributor: 'Theo',
  },
  // Add more entries as needed
];

export function getAuditLog(): AuditEntry[] {
  return auditLog.map((entry, index) => ({
    id: entry.id ?? uuidv4(), // ✅ Now matches declared type
    from: entry.from ?? 'unknown',
    to: entry.to ?? 'unknown',
    timestamp: entry.timestamp ?? Date.now(),
    dryRun: entry.dryRun ?? false,
    contributor: entry.contributor ?? 'unknown',
  }));
}