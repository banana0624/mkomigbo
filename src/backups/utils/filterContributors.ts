// src/backups/utils/filterContributors.ts

import { Contributor } from '../types'

export const filterByBadge = (contributors: Contributor[], badge: string) =>
  contributors.filter(c => c.badgeState === badge)

export const filterByOverlayStatus = (contributors: Contributor[], status: string) =>
  contributors.filter(c => c.overlayStatus === status)
