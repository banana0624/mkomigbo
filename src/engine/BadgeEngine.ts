// project-root/src/engine/BadgeEngine.ts

export type BadgeType = 'streak' | 'dryRunMaster';
export type BadgeLevel = 'gold' | 'silver' | 'bronze';

interface BadgeConfig {
  type: BadgeType;
  level?: BadgeLevel;
  label: string;
}

export const getBadgeLabel = (type: BadgeType, level?: BadgeLevel): string => {
  if (type === 'streak') {
    return `Streak (${level ?? 'bronze'})`;
  }
  return 'Dry Run Master';
};

export const generateBadgeConfig = (type: BadgeType, level?: BadgeLevel): BadgeConfig => ({
  type,
  level,
  label: getBadgeLabel(type, level),
});
