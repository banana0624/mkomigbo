// project-root/src/backups/types.ts

import type { BackupEntry } from '../models/backupEntry.js'

export type { BackupEntry } // ✅ Optional: re-export for convenience

// Contributor used by backup summaries and entries
export interface Contributor {
  id: string
  name: string
  badgeState?: 'newcomer' | 'momentum' | 'trailblazer' | string
  overlayStatus?: 'initial' | 'guided' | 'complete' | string
  rhythmScore?: number
  role?: 'admin' | 'editor' | 'viewer'
}

// Summary of backups for dry-run and dashboards
export interface ContributorImpact {
  totalContributors: number
  recentJoins: number
  badgesIssued: number
}

export interface BackupSummary {
  total: number                // ✅ number of backups
  latest: string               // ✅ ISO timestamp of latest backup
  sizeEstimate?: string        // e.g., "1.2 GB"
  totalEntries?: number        // optional alias of total
  lastBackup?: string          // optional alias of latest
  entries?: BackupEntry[]      // optional detailed list
  contributorImpact?: ContributorImpact
}

// Re-exports for convenience if you want to import everything from './types'
// export type { BackupEntry } from '../models/backupEntry.js'


