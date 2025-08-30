// project-root/src/api/controllers/getBackupSummary.ts

import { GetBackupSummaryResponse } from '../../types/api/responses'
import { getMockBackupSummary } from '../../backups/mockData'
import { BackupEntry, BackupSummary } from '../../models/backupSummary'

export const mockBackupEntries: BackupEntry[] = [
  {
    id: 'b001',
    contributorId: 'c001',
    size: '1.2 GB',
    timestamp: '2025-08-20T14:32:00Z',
  },
  {
    id: 'b002',
    contributorId: 'c002',
    size: '850 MB',
    timestamp: '2025-08-21T09:15:00Z',
  },
  {
    id: 'b003',
    contributorId: 'c003',
    size: '2.5 GB',
    timestamp: '2025-08-22T17:48:00Z',
  },
]

export const getBackupSummary = (): GetBackupSummaryResponse => {
  return getMockBackupSummary()
}
