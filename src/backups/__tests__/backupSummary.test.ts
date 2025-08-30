// src/backups/__tests__/backupSummary.test.ts

import { getMockBackupSummary } from '../mockData'
import { validateBackupSummary } from '../utils/validateBackupSummary'

describe('BackupSummary validation', () => {
  it('should validate mock summary structure', () => {
    const summary = getMockBackupSummary()
    expect(validateBackupSummary(summary)).toBe(true)
  })
})
