// project-root/src/backups/types.ts

export interface BackupEntry {
  id: string
  contributorId: string
  size: string
  timestamp: string
  status: 'completed' | 'failed' | 'pending' // ← Add this line
  stage: 'created' | 'validated' | 'archived'
}

export interface Contributor {
  id: string
  name: string
  badgeState: 'newcomer' | 'momentum' | 'trailblazer'
  overlayStatus: 'initial' | 'guided' | 'complete'
  rhythmScore: number
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
