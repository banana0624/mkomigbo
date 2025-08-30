// project-root/src/models/Contributor.ts

export interface Contributor {
  id: string;
  name: string;
  avatarUrl?: string;
  role?: 'admin' | 'editor' | 'viewer';
  joinedAt?: number; // Unix timestamp
  badges?: string[]; // e.g., ['onboarded', 'audit-champion']
  streakCount?: number;
  dryRunRatio?: number;
}

// models/contributor.ts

export interface Contributor {
  id: string
  name: string
  role?: string
  avatarUrl?: string
  badgeState: 'newcomer' | 'momentum' | 'trailblazer'
  overlayStatus: 'initial' | 'guided' | 'complete'
  rhythmScore?: number
}

