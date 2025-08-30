// src/backups/utils/generateHeatmap.ts

import { Contributor } from '../types'

export const generateHeatmapData = (contributors: Contributor[]) => {
  return contributors.map(c => ({
    id: c.id,
    rhythmScore: c.rhythmScore,
    overlayStatus: c.overlayStatus,
    badgeState: c.badgeState,
  }))
}
