// project-root/src/hooks/useFilteredAuditEntries.ts

import { useMemo } from 'react';
import type { AuditEntry } from '../types/audit/auditTypes';

interface Filters {
  contributor: string | null;
  stage: string | null;
  startDate: string | null;
  endDate: string | null;
  dryRunOnly?: boolean; // ✅ Add this optional field
}

export function useFilteredAuditEntries(entries: AuditEntry[], filters: Filters): AuditEntry[] {
  return useMemo(() => {
    const startTimestamp = filters.startDate ? new Date(filters.startDate).getTime() : null;
    const endTimestamp = filters.endDate ? new Date(filters.endDate).getTime() : null;

    return entries.filter((entry) => {
      const contributorMatch = filters.contributor ? entry.contributor === filters.contributor : true;
      const stageMatch = filters.stage ? entry.to === filters.stage : true;
      const startMatch = startTimestamp !== null ? entry.timestamp >= startTimestamp : true;
      const endMatch = endTimestamp !== null ? entry.timestamp <= endTimestamp : true;
      const dryRunMatch = filters.dryRunOnly ? entry.dryRun === true : true;

      return contributorMatch && stageMatch && startMatch && endMatch && dryRunMatch;
    });
  }, [entries, filters]);
}