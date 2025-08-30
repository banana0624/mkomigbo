// project-root/src/types/backup/backupTypes.ts

export type BackupState = 'created' | 'validated' | 'archived'
export type BackupStatus = 'completed' | 'failed' | 'pending'

export interface BackupEntry {
  id: string
  contributorId: string
  size: string
  timestamp: string
  status: BackupStatus
  stage: BackupState
}
