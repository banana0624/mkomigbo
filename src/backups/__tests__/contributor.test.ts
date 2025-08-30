// src/backups/__tests__/contributor.test.ts

import { mockContributors } from '../mockData'

describe('Contributor structure', () => {
  it('should have valid fields', () => {
    mockContributors.forEach(c => {
      expect(c).toHaveProperty('id')
      expect(c).toHaveProperty('name')
      expect(c).toHaveProperty('badgeState')
      expect(c).toHaveProperty('overlayStatus')
      expect(c).toHaveProperty('rhythmScore')
    })
  })

  it('should have valid rhythmScore range', () => {
    mockContributors.forEach(c => {
      expect(typeof c.rhythmScore).toBe('number')
      expect(c.rhythmScore).toBeGreaterThanOrEqual(0)
      expect(c.rhythmScore).toBeLessThanOrEqual(100)
    })
  })
})
