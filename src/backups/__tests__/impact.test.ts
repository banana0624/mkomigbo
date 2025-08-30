// src/backups/__tests__/impact.test.ts

import { getMockBackupSummary } from '../mockData'

describe('Contributor impact metrics', () => {
  const summary = getMockBackupSummary()

  it('should match contributor count', () => {
    expect(summary.contributorImpact.totalContributors).toBe(summary.entries.length)
  })

  it('should count badges correctly', () => {
    const issued = summary.entries.filter(e => e.contributorId !== 'c001').length
    expect(summary.contributorImpact.badgesIssued).toBeGreaterThanOrEqual(issued)
  })
})
