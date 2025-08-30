// src/backups/utils/simulateOnboarding.ts

import { Contributor } from '../types'

export const simulateOnboardingProgress = (contributor: Contributor) => {
  const stages = ['initial', 'guided', 'complete']
  const currentIndex = stages.indexOf(contributor.overlayStatus)
  return stages.slice(currentIndex)
}
