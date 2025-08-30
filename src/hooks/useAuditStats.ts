// project-root/src/hooks/useAuditStats.ts

import type { AuditEntry } from '../types/audit/auditTypes';

export function useAuditStats(entries: AuditEntry[] = []) {
  const contributorDensity = entries.reduce((acc, entry) => {
    acc[entry.contributor] = (acc[entry.contributor] ?? 0) + 1;
    return acc;
  }, {} as Record<string, number>);

  const dryRunCount = entries.filter((e) => e.dryRun).length;
  const dryRunRatio = entries.length ? dryRunCount / entries.length : 0;

  const lifecycleCounts = entries.reduce((acc, entry) => {
    acc[entry.to] = (acc[entry.to] ?? 0) + 1;
    return acc;
  }, {} as Record<string, number>);

  return { contributorDensity, dryRunRatio, lifecycleCounts };
}
