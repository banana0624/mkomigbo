// project-root/src/types/api/responses.ts

import { BackupSummary } from '../../models/backupSummary'

// src/types/api/responses.ts

import { Contributor } from '../../backups/types'

export type GetContributorsResponse = Contributor[]


export type GetBackupSummaryResponse = BackupSummary

export interface BackupEntry {
  id: string
  timestamp: string
  status: 'success' | 'failure' | 'pending'
  sizeInBytes: number
  notes?: string
}

export interface GetBackupEntriesResponse {
  entries: BackupEntry[]
}

export interface GetBackupEntriesResponse {
    entries: BackupEntry[];
  }
  

