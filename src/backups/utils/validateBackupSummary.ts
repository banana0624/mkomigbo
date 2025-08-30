// src/backups/utils/validateBackupSummary.ts

import { BackupSummary } from '../types'

export const validateBackupSummary = (summary: BackupSummary): boolean => {
  return (
    typeof summary.totalEntries === 'number' &&
    typeof summary.lastBackup === 'string' &&
    typeof summary.sizeEstimate === 'string' &&
    Array.isArray(summary.entries) &&
    typeof summary.contributorImpact?.totalContributors === 'number'
  )
}
