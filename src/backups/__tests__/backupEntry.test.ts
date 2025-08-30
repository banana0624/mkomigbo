// src/backups/__tests__/backupEntry.test.ts

import { mockBackupEntries } from '../mockData'

describe('BackupEntry structure', () => {
  it('should have valid fields', () => {
    mockBackupEntries.forEach(entry => {
      expect(entry).toHaveProperty('id')
      expect(entry).toHaveProperty('contributorId')
      expect(entry).toHaveProperty('size')
      expect(entry).toHaveProperty('timestamp')
    })
  })

  it('should have valid timestamp format', () => {
    mockBackupEntries.forEach(entry => {
      const date = new Date(entry.timestamp)
      expect(date.toString()).not.toBe('Invalid Date')
    })
  })
})
