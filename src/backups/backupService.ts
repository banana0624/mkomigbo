// project-roor/src/backups/backupService.ts

import { BackupEntry } from '../models/backupEntry'
import { parseSize } from './backupUtils'

export const calculateTotalSizeMB = (entries: BackupEntry[]): number => {
  return entries.reduce((sum, entry) => sum + parseSize(entry.size), 0)
}

export const getLatestBackup = (entries: BackupEntry[]): BackupEntry => {
  return entries.reduce((latest, entry) =>
    new Date(entry.timestamp) > new Date(latest.timestamp) ? entry : latest
  )
}


