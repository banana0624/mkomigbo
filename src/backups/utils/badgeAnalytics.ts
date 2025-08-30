// src/backups/utils/badgeAnalytics.ts

import { Contributor } from '../types'

export const getBadgeDistribution = (contributors: Contributor[]) => {
  return contributors.reduce((acc, c) => {
    acc[c.badgeState] = (acc[c.badgeState] || 0) + 1
    return acc
  }, {} as Record<string, number>)
}
