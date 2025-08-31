// project-root/src/backups/mockData.ts

import type { BackupEntry, BackupSummary, Contributor } from './types.js';

export const mockContributors: Contributor[] = [
  { id: 'c001', name: 'Amina',  badgeState: 'newcomer',   overlayStatus: 'initial',  rhythmScore: 12 },
  { id: 'c002', name: 'Kwame',  badgeState: 'momentum',   overlayStatus: 'guided',   rhythmScore: 47 },
  { id: 'c003', name: 'Zainab', badgeState: 'trailblazer',overlayStatus: 'complete', rhythmScore: 89 },
];

export const mockBackupEntries: BackupEntry[] = [
  {
    id: 'b001',
    contributorId: 'c001',
    size: '1.2 GB',
    timestamp: '2025-08-20T14:32:00Z',
    status: 'completed',
    stage: 'validated',
    state: 'completed',
  },
  {
    id: 'b002',
    contributorId: 'c002',
    size: '850 MB',
    timestamp: '2025-08-21T09:15:00Z',
    status: 'failed',
    stage: 'archived',
    state: 'failed',
  },
  {
    id: 'b003',
    contributorId: 'c003',
    size: '2.5 GB',
    timestamp: '2025-08-22T17:48:00Z',
    status: 'pending',
    stage: 'created',
    state: 'pending',
  },
];

const parseSize = (size: string): number => {
  const [value, unit] = size.split(' ');
  const multiplier = unit === 'GB' ? 1024 : 1; // MB
  return parseFloat(value) * multiplier;
};

export const getMockBackupSummary = (): BackupSummary => {
  const entries = mockBackupEntries;

  const totalSizeMB = entries.reduce((sum, entry) => sum + parseSize(entry.size), 0);
  const sizeEstimate = `${(totalSizeMB / 1024).toFixed(2)} GB`;

  const latestEntry = entries.reduce((latest, entry) =>
    new Date(entry.timestamp) > new Date(latest.timestamp) ? entry : latest
  );

  return {
    total: entries.length,
    latest: latestEntry.timestamp,
    sizeEstimate,
    totalEntries: entries.length,
    lastBackup: latestEntry.timestamp,
    entries,
    contributorImpact: {
      totalContributors: mockContributors.length,
      recentJoins: mockContributors.filter(c => c.badgeState === 'newcomer').length,
      badgesIssued: mockContributors.filter(c => c.badgeState !== 'newcomer').length,
    },
  };
};
