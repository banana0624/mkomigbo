// src/backups/utils/analyzeRhythm.ts

import { Contributor } from '../types'

export const getRhythmAnalytics = (contributors: Contributor[]) => {
  const total = contributors.length
  const avgRhythm = contributors.reduce((sum, c) => sum + c.rhythmScore, 0) / total
  const topPerformers = contributors.filter(c => c.rhythmScore > 70)

  return {
    total,
    avgRhythm: parseFloat(avgRhythm.toFixed(2)),
    topPerformers,
  }
}
