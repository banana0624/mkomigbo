// project-root/src/hooks/useAuditEntries.ts

import { useEffect, useState } from 'react';
import { AuditEntry } from '../models/AuditEntry';
import { Contributor } from '../models/Contributor';

export const useAuditEntries = (contributorId: string) => {
  const [entries, setEntries] = useState<AuditEntry[]>([]);

  useEffect(() => {
    // Replace with real API call
    const fetchEntries = async () => {
      const mockContributor: Contributor = {
        id: contributorId,
        name: 'Mock Contributor',
      };

const mockData: AuditEntry[] = [
  {
    id: '1',
    contributorId,
    timestamp: Date.now(),
    action: 'dry_run',
    contributor: contributorId, // ✅ string, not object
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
