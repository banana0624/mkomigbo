// project-root/src/utils/audit/validateAuditEntry.ts

import type { AuditEntry } from '../../types/audit/auditTypes';

export function validateAuditEntry(entry: AuditEntry): string[] {
  const errors: string[] = [];

  if (!entry.id) errors.push('Missing ID');
  if (!entry.from) errors.push('Missing "from" stage');
  if (!entry.to) errors.push('Missing "to" stage');
  if (!entry.timestamp) errors.push('Missing timestamp');
  if (!entry.contributor) errors.push('Missing contributor');

  return errors;
}