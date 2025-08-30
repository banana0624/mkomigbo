// project-root/scripts/onboarding/contributorBadgeTrigger.ts

import { runBadgeAnimation } from './animations';
import { resolveBadge, type Milestone } from './badgeRegistry';

export const triggerContributorBadge = (milestone: Milestone): void => {
  const { elementId, animation } = resolveBadge(milestone);
  runBadgeAnimation(elementId, animation);
  // Side-effect logging only; audit tracing happens in auditHeatmap.ts
  // Keep console log for contributor celebration in dev
  // eslint-disable-next-line no-console
  console.log(`🎉 Badge triggered for milestone: ${milestone}`);
};

