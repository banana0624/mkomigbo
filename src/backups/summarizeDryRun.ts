// project-root/src/backups/summarizeDryRun.ts

import type { BackupSummary } from './types.js'
import { getMockBackupSummary } from './mockData.js'
import { logger } from '../utils/logger.js'

export async function summarizeBackupsDryRun(): Promise<BackupSummary> {
  const mockSummary = getMockBackupSummary()
  logger.info('[DRY-RUN] Backup summary preview:')
  logger.info(`[DRY-RUN] Total backups: ${mockSummary.total}`)
  logger.info(`[DRY-RUN] Latest backup: ${mockSummary.latest}`)
  logger.info(`[DRY-RUN] Size estimate: ${mockSummary.sizeEstimate}`)
  return mockSummary
}
