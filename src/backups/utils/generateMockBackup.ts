// src/backups/utils/generateMockBackup.ts

// src/backups/utils/generateMockBackupEntries.ts

import { BackupEntry } from '../types'

export const generateMockBackupEntries = (
  count: number,
  contributorIds: string[]
): BackupEntry[] => {
  const now = Date.now()
  const statusOptions: BackupEntry['status'][] = ['completed', 'failed', 'pending']
  const stageOptions: BackupEntry['stage'][] = ['created', 'validated', 'archived']

  return Array.from({ length: count }, (_, i) => {
    const sizeMB = Math.floor(Math.random() * 2500) + 500
    const size = sizeMB >= 1024 ? `${(sizeMB / 1024).toFixed(1)} GB` : `${sizeMB} MB`

    return {
      id: `b${i + 100}`,
      contributorId: contributorIds[i % contributorIds.length],
      size,
      timestamp: new Date(now - i * 86400000).toISOString(),
      status: statusOptions[i % statusOptions.length],
      stage: stageOptions[i % stageOptions.length],
    }
  })
}
