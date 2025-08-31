// project-root/src/models/Contributor.ts

export interface Contributor {
  id: string;
  name: string;
  avatarUrl?: string;
  role?: 'admin' | 'editor' | 'viewer';
  joinedAt?: number; // Unix timestamp
  badges?: string[]; // e.g., ['onboarded', 'audit-champion']
  badgeState: 'newcomer' | 'momentum' | 'trailblazer'
  streakCount?: number;
  dryRunRatio?: number;
  overlayStatus?: string;
  rhythmScore?: number
}
