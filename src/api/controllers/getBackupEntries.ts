// project-root/src/api/controllers/getBackupEntries.ts

import { GetBackupEntriesResponse } from '../../types/api/responses'
import { getMockBackupSummary } from '../../backups/mockData'
import { parseSize } from '../../backups/backupUtils'
import { BackupStatus } from '../../types/backup/backupTypes'

export const getBackupEntries = (): GetBackupEntriesResponse => {
  const rawEntries = getMockBackupSummary().entries

  const transformed = rawEntries.map(entry => ({
    id: entry.id,
    timestamp: entry.timestamp,
    status: normalizeStatus(entry.status),
    sizeInBytes: parseSize(entry.size),
    notes: entry.stage ?? 'No stage provided',
  }))

  return { entries: transformed }
}

// 🎯 Normalize status to expected enum values
const normalizeStatus = (status?: BackupStatus): 'pending' | 'success' | 'failure' => {
  const statusMap: Record<BackupStatus, 'pending' | 'success' | 'failure'> = {
    completed: 'success',
    failed: 'failure',
    pending: 'pending',
  }

  return status ? statusMap[status] : 'pending'
}
