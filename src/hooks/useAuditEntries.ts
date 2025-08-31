// project-root/src/hooks/useAuditEntries.ts

import { useEffect, useState } from 'react';
import { AuditEntry } from '../models/AuditEntry.ts';
import { Contributor } from '../models/Contributor.ts';

export const useAuditEntries = (contributorId: string) => {
  const [entries, setEntries] = useState<AuditEntry[]>([]);

  useEffect(() => {
    // Replace with real API call
    const fetchEntries = async () => {
      const mockData: AuditEntry[] = [
        {
          id: '1',
          contributorId,
          timestamp: Date.now(),
          action: 'dry_run',
          contributor: contributorId,
        },
        {
          id: '2',
          contributorId,
          timestamp: Date.now(),
          action: 'commit',
          contributor: contributorId,
        },
      ];

      setEntries(mockData);
    };

    fetchEntries();
  }, [contributorId]);

  return entries;
};
