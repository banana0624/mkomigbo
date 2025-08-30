// project-root/scripts/onboarding/badgeRegistry.ts

import type { BadgeAnimation } from './animations';

export type Milestone =
  | 'first-commit'
  | 'first-issue'
  | 'first-pr'
  | 'first-merge'
  | 'ten-contributions';

type RegistryItem = { elementId: string; animation: BadgeAnimation };

const REGISTRY: Record<Milestone, RegistryItem> = {
  'first-commit': { elementId: 'badge-first-commit', animation: 'pulse' },
  'first-issue': { elementId: 'badge-first-issue', animation: 'glow' },
  'first-pr': { elementId: 'badge-first-pr', animation: 'bounce' },
  'first-merge': { elementId: 'badge-first-merge', animation: 'glow' },
  'ten-contributions': { elementId: 'badge-ten-contributions', animation: 'pulse' },
};

export const resolveBadge = (milestone: Milestone): RegistryItem => REGISTRY[milestone];
