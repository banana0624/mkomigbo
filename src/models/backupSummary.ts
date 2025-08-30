// project-root/src/models/backupSummary.ts

export interface BackupEntry {
  id: string
  contributorId: string
  size: string
  timestamp: string
}

export interface BackupSummary {
  totalEntries: number
  lastBackup: string
  sizeEstimate: string
  entries: BackupEntry[]
  contributorImpact: {
    totalContributors: number
    recentJoins: number
    badgesIssued: number
  }
}

