// project-root/src/data/badgeData.ts

export type BadgeTier = 'Newcomer' | 'Explorer' | 'Builder' | 'Mentor' | 'Visionary';

export interface Badge {
  id: string;
  label: string;
  description: string;
  tier: BadgeTier;
  icon: string; // token or SVG path
  milestone: string; // e.g. "First PR merged"
}

export const badgeData: Badge[] = [
  {
    id: 'newcomer',
    label: 'Newcomer',
    description: 'Completed onboarding overlay',
    tier: 'Newcomer',
    icon: 'icon.newcomer',
    milestone: 'First login + overlay complete',
  },
  {
    id: 'explorer',
    label: 'Explorer',
    description: 'Explored 5 modules and left feedback',
    tier: 'Explorer',
    icon: 'icon.explorer',
    milestone: '5 modules viewed',
  },
  {
    id: 'builder',
    label: 'Builder',
    description: 'Merged first PR',
    tier: 'Builder',
    icon: 'icon.builder',
    milestone: 'First contribution merged',
  },
  {
    id: 'mentor',
    label: 'Mentor',
    description: 'Guided a new contributor',
    tier: 'Mentor',
    icon: 'icon.mentor',
    milestone: 'Mentored onboarding',
  },
  {
    id: 'visionary',
    label: 'Visionary',
    description: 'Proposed a system-level enhancement',
    tier: 'Visionary',
    icon: 'icon.visionary',
    milestone: 'Proposal accepted',
  },
];
