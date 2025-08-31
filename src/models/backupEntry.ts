// project-root/src/models/backupEntry.ts

// project-root/src/models/backupEntry.ts

import type { BackupState } from './BackupState.js';
import type { Contributor } from '../backups/types.js';

export interface BackupEntry {
  id: string;
  state: BackupState;
  timestamp: string;
  size: string;
  stage: string;
  status: string;
  source?: string;
  auditTrail?: string[];
  contributorId: string;
  contributor?: Contributor;
}
