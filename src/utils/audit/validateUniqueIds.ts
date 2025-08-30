// project-root/src/utils/audit/validateUniqueIds.ts

import type { AuditEntry } from '../../types/audit/auditTypes';

export function validateUniqueIds(entries: AuditEntry[]): string[] {
  const seen = new Set<string>();
  const duplicates: string[] = [];

  entries.forEach((entry) => {
    if (seen.has(entry.id)) duplicates.push(entry.id);
    seen.add(entry.id);
  });

  return duplicates;
}