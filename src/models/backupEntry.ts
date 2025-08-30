// project-root/src/models/backupEntry.ts

export interface BackupEntry {
  id: string
  timestamp: string
  size: string
  stage: 'created' | 'validated' | 'archived'
  status: 'pending' | 'completed' | 'failed'
  source?: string
  auditTrail?: string[]
  contributorId: string
  contributor?: Contributor
}
