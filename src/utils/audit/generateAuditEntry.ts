// project-root/src/utils/audit/generateAuditEntry.ts

import { generateAuditId } from './generateAuditId';
import type { AuditEntry } from '../../types/audit/auditTypes';

export function generateAuditEntry(params: Partial<AuditEntry>): AuditEntry {
  const entry: AuditEntry = {
    id: '',
    from: params.from ?? 'pending',
    to: params.to ?? 'validated',
    timestamp: params.timestamp ?? Date.now(),
    dryRun: params.dryRun ?? false,
    contributor: params.contributor ?? 'unknown',
  };

  entry.id = generateAuditId(entry);
  return entry;
}