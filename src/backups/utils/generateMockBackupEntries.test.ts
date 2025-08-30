// project-root/src/backups/utils/generateMockBackupEntries.test.ts

import { generateMockBackupEntries } from './generateMockBackup'

describe('generateMockBackupEntries', () => {
  const contributorIds = ['c001', 'c002', 'c003']

  it('should generate the correct number of entries', () => {
    const entries = generateMockBackupEntries(5, contributorIds)
    expect(entries).toHaveLength(5)
  })

  it('should assign valid contributorIds', () => {
    const entries = generateMockBackupEntries(10, contributorIds)
    entries.forEach(entry => {
      expect(contributorIds).toContain(entry.contributorId)
    })
  })

  it('should include status and stage', () => {
    const entries = generateMockBackupEntries(3, contributorIds)
    entries.forEach(entry => {
      expect(entry).toHaveProperty('status')
      expect(entry).toHaveProperty('stage')
    })
  })

  it('should format size as MB or GB', () => {
    const entries = generateMockBackupEntries(10, contributorIds)
    entries.forEach(entry => {
      expect(entry.size).toMatch(/(MB|GB)$/)
    })
  })
})
